import ApiService from "@/services/api.service";
import { Header } from "@/components/Utils/Datatable/Datatable.vue";
import qs from "qs";

interface DataTableMixin {
    endpoint: string | null;
    sortField: string;
    sortDirection: 'ASC' | 'DESC';
    pagination: {
        currentPage: number;
        pageSize: number;
        totalItem: number;
        totalPage: number;
    };
    pageSizeOptions: Array<{ label: string; value: number }>;
    filter: string;
    loading: boolean;
    rowData: Array<Record<string, unknown>>;
    loadData(this: DataTableMixin): void;
    sortTable(this: DataTableMixin, selectedField: Header): void;
    updateFilter(this: DataTableMixin): void;
    paginationClick(this: DataTableMixin, page: number): void;
    getLoadDataParameters(this: DataTableMixin): Record<string, unknown>;
}

export default {
    data() {
        return {
            filterField: 'name',
            sortField: '',
            sortDirection: 'DESC' as 'ASC' | 'DESC',
            pagination: {
                currentPage: 1,
                pageSize: 10,
                totalItem: 0,
                totalPage: 0
            },
            pageSizeOptions: [
                { label: "5", value: 5 },
                { label: "10", value: 10 },
                { label: "20", value: 20 }
            ],
            filter: '',
            loading: false,
            rowData: []
        };
    },
    methods: {
        sortTable(this: DataTableMixin, selectedField: Header): void {
            if (!selectedField.sortable) {
                return;
            }
            if (this.sortField === selectedField.key) {
                this.sortDirection = this.sortDirection === 'DESC' ? 'ASC' : 'DESC';
            } else {
                this.sortDirection = 'DESC';
            }
            this.sortField = selectedField.key;
            this.loadData();
        },
        updateFilter(this: DataTableMixin): void {
            this.pagination.currentPage = 1;
            this.loadData();
        },
        paginationClick(this: DataTableMixin, page: number): void {
            this.pagination.currentPage = page;
            this.loadData();
        },
        getLoadDataParameters(this: DataTableMixin): Record<string, unknown> {
            const params: Record<string, unknown> = {};
            if (this.pagination.currentPage) {
                params['page'] = this.pagination.currentPage;
            }
            if (this.pagination.pageSize) {
                params['itemsPerPage'] = this.pagination.pageSize;
            }
            if (this.filter) {
                params[this.filterField] = this.filter;
            }
            if (this.sortField) {
                qs.stringify(params['order'] = { [this.sortField]: this.sortDirection });
            }
            return params;
        },
        loadData(this: DataTableMixin) {
            this.loading = true;
            const params: Record<string, unknown> = {
                header: { 'accept': 'application/ld+json' },
                params: this.getLoadDataParameters(),
                paramsSerializer: qs.stringify
            };
            ApiService.get(this.endpoint, params)
                .then((result) => {
                    return result.data;
                })
                .then((remoteRowData: { 'hydra:member': Array<Record<string, unknown>>; 'hydra:totalItems': number }) => {
                    this.rowData = remoteRowData['hydra:member'];
                    this.pagination.totalItem = remoteRowData['hydra:totalItems'];
                    this.pagination.totalPage = this.pagination.totalItem / this.pagination.pageSize;
                    this.loading = false;
                })
                .catch(() => {
                    this.loading = false;
                });
        }
    },
    beforeMount(this: DataTableMixin) {
        this.loadData();
    }
};
