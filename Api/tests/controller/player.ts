import chai from 'chai';
import {describe, it} from 'mocha';
import chaiHttp from 'chai-http';
import app from '../../src/app';
import {Daedalus} from '../../src/models/daedalus.model';

chai.use(chaiHttp);
const expect = chai.expect;

describe('/POST player', () => {
    it('it should create a new user player in an existing daedalus', done => {
        let daedalus: Daedalus;
        Daedalus.create().then((model) => {
            daedalus = model
            chai.request(app)
                .post('/players')
                .send({
                    "daedalus": daedalus.getId,
                    "character": "Ian"
                })
                .end((err, res) => {
                    expect(res).to.have.status(201);
                    expect(res.body).to.be.a('object');
                    done();
                })
        });
    })
});
