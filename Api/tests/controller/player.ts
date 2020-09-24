import chai from 'chai';
import {describe, it} from 'mocha';
import chaiHttp from 'chai-http';
import app from '../../src/app';
import {Daedalus} from '../../src/models/daedalus.model';
import {Player} from '../../src/models/player.model';

chai.use(chaiHttp);
const expect = chai.expect;

describe('/POST player', () => {
    it('it should create a new user player in an existing daedalus', done => {
        Player.create();
        Daedalus.create();
        chai.timeout(10000);
        chai.request(app)
            .post('/players')
            .set({
                character: 'ian',
            })
            .end((err, res) => {
                expect(res).to.have.status(201);
                expect(res.body).to.be.a('array');
                expect(res.body.length).to.be.equal(0);
                done();
            });
    });
});
