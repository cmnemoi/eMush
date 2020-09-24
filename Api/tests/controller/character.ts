import chai from 'chai';
import {describe, it} from 'mocha';
import chaiHttp from 'chai-http';
import app from '../../src/app';

chai.use(chaiHttp);
const expect = chai.expect;

describe('/GET characters', () => {
    it('it should GET all the characters', done => {
        chai.request(app)
            .get('/characters')
            .end((err, res) => {
                expect(res).to.have.status(200);
                expect(res.body).to.be.a('array');
                expect(res.body.length).to.be.equal(0);
                done();
            });
    });
});
