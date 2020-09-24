import chai from 'chai';
import {before, describe, it} from 'mocha';
import chaiHttp from 'chai-http';
import app from '../../src/app';
import {Daedalus} from '../../src/models/daedalus.model';
import {Character} from '../../src/enums/characters.enum';

chai.use(chaiHttp);
const expect = chai.expect;

describe('/POST player', () => {
    let daedalus: Daedalus;

    before(async () => {
        await Daedalus.create().then(value => (daedalus = value));
    });

    it('it should create a new user player in an existing daedalus', done => {
        chai.request(app)
            .post('/players')
            .send({
                daedalus: daedalus.getId,
                character: Character.IAN,
            })
            .end((err, res) => {
                expect(res).to.have.status(201);
                expect(res.body).to.be.an('object');
                done();
            });
    });

    it('it should issue a validation problem : missing character', done => {
        chai.request(app)
            .post('/players')
            .send({
                daedalus: daedalus.getId,
            })
            .end((err, res) => {
                expect(res).to.have.status(422);
                expect(res.body).to.be.an('object');
                expect(res.body)
                    .to.have.nested.property('errors[0].param')
                    .equal('character');
                done();
            });
    });

    it('it should issue a validation problem : invalid character', done => {
        chai.request(app)
            .post('/players')
            .send({
                daedalus: daedalus.getId,
                character: 'unknown',
            })
            .end((err, res) => {
                expect(res).to.have.status(422);
                expect(res.body).to.be.an('object');
                expect(res.body)
                    .to.have.nested.property('errors[0].param')
                    .equal('character');
                done();
            });
    });

    it('it should issue a validation problem : missing daedalus', done => {
        chai.request(app)
            .post('/players')
            .send({
                character: Character.ANDIE,
            })
            .end((err, res) => {
                expect(res).to.have.status(422);
                expect(res.body).to.be.an('object');
                expect(res.body)
                    .to.have.nested.property('errors[0].param')
                    .equal('daedalus');
                done();
            });
    });

    it('it should issue a validation problem : invalid daedalus', done => {
        chai.request(app)
            .post('/players')
            .send({
                daedalus: 0,
                character: Character.ANDIE,
            })
            .end((err, res) => {
                expect(res).to.have.status(422);
                expect(res.body).to.be.an('object');
                expect(res.body)
                    .to.have.nested.property('errors[0].param')
                    .equal('daedalus');
                done();
            });
    });
});
