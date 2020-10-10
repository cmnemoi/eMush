export default {
    openapi: '3.0.0',
    info: {
        description: 'Mush APIs',
        version: '1.0.0',
        title: 'Swagger Mush',
    },
    servers: [
        {
            url: 'http://localhost:8080/api/v1',
        },
    ],
    tags: [
        {
            name: 'Actions',
            description: 'Api to make actions',
        },
        {
            name: 'Player',
            description: 'Api to manipulate a user game',
        },
        {
            name: 'Daedalus',
            description: 'Api to manipulate a Daedalus',
        },
    ],
    paths: {
        '/action': {
            post: {
                tags: ['Actions'],
                security: [{bearer: []}],
                summary: 'Create a new action',
                requestBody: {
                    required: true,
                    content: {
                        'application/json': {
                            schema: {
                                type: 'object',
                                required: ['action'],
                                properties: {
                                    action: {
                                        type: 'string',
                                    },
                                    params: {
                                        type: 'object',
                                        properties: {
                                            item: {
                                                type: 'string',
                                            },
                                            door: {
                                                type: 'string',
                                            },
                                        },
                                    },
                                },
                            },
                        },
                    },
                },
                responses: {
                    '200': {
                        description: 'Action has been performed',
                    },
                    '401': {
                        $ref: '#/components/responses/UnauthorizedError',
                    },
                    '422': {
                        description: 'Invalid parameters',
                    },
                },
            },
        },
        '/player/{id}': {
            get: {
                tags: ['Player'],
                security: [{bearer: []}],
                summary: 'Get user informations',
                parameters: [
                    {
                        in: 'path',
                        name: 'id',
                        required: true,
                        schema: {
                            type: 'integer',
                        },
                    },
                ],
                responses: {
                    '200': {
                        description: 'Player information',
                    },
                    '404': {
                        description: 'Not found',
                    },
                },
            },
        },
        '/player': {
            post: {
                tags: ['Player'],
                security: [{bearer: []}],
                summary: 'Create a new player',
                requestBody: {
                    required: true,
                    content: {
                        'application/json': {
                            schema: {
                                type: 'object',
                                required: ['daedalus', 'character'],
                                properties: {
                                    daedalus: {
                                        type: 'integer',
                                    },
                                    character: {
                                        type: 'string',
                                    },
                                },
                            },
                        },
                    },
                },
                responses: {
                    '200': {
                        description: 'Action has been performed',
                    },
                    '401': {
                        $ref: '#/components/responses/UnauthorizedError',
                    },
                    '422': {
                        description: 'Invalid parameters',
                    },
                },
            },
        },
        '/daedalus/{id}': {
            get: {
                tags: ['Daedalus'],
                security: [{bearer: []}],
                summary: 'Get daedalus informations',
                parameters: [
                    {
                        in: 'path',
                        name: 'id',
                        required: true,
                        schema: {
                            type: 'integer',
                        },
                    },
                ],
                responses: {
                    '200': {
                        description: 'Daedalus information',
                    },
                    '404': {
                        description: 'Not found',
                    },
                },
            },
        },
        '/daedalus': {
            post: {
                tags: ['Daedalus'],
                security: [{bearer: []}],
                summary: 'Create a new Daedalus',
                responses: {
                    '200': {
                        description: 'Action has been performed',
                    },
                    '401': {
                        $ref: '#/components/responses/UnauthorizedError',
                    },
                    '422': {
                        description: 'Invalid parameters',
                    },
                },
            },
        },
        '/login': {
            post: {
                tags: ['user'],
                summary: 'Logs user into the system',
                requestBody: {
                    required: true,
                    content: {
                        'application/json': {
                            schema: {
                                type: 'object',
                                required: ['username'],
                                properties: {
                                    username: {
                                        type: 'string',
                                    },
                                },
                            },
                        },
                    },
                },
                responses: {
                    '200': {
                        description: 'successful operation',
                        schema: {
                            type: 'string',
                        },
                        headers: {
                            'X-Rate-Limit': {
                                type: 'integer',
                                format: 'int32',
                                description:
                                    'calls per hour allowed by the user',
                            },
                            'X-Expires-After': {
                                type: 'string',
                                format: 'date-time',
                                description: 'date in UTC when token expires',
                            },
                        },
                    },
                    '400': {
                        description: 'Invalid username/password supplied',
                    },
                },
            },
        },
    },
    components: {
        securitySchemes: {
            bearer: {
                type: 'http',
                scheme: 'bearer',
            },
        },
        responses: {
            UnauthorizedError: {
                description: 'Access token is missing or invalid',
            },
            UnprocessableEntity: {
                description: 'Access token is missing or invalid',
            },
        },
    },
};
