<?php

class Doctrine_Ticket_1323b2_TestCase extends Doctrine_UnitTestCase
{
    public function prepareTables()
    {
        $this->tables   = array();
        $this->tables[] = 'Concept';
        $this->tables[] = 'ConceptRelation';
        parent::prepareTables();
    }

    public function prepareData()
    {
    }

    /**
     * setting some polyhierarchical relations
     */
    public function resetData()
    {
        $q = Doctrine_Query::create();
        $q->delete()->from('ConceptRelation')->execute();
        $q = Doctrine_Query::create();
        $q->delete()->from('Concept')->execute();

        $concepts = array('Woodworking', 'Metalworking',
                        'Submetalworking 1', 'Submetalworking 2',
                        'Subwoodworking 1', 'Subwoodworking 2',
                        'Surfaceworking',
                        'drilled', 'welded', 'turned');

        foreach ($concepts as $concept) {
            $c                     = new Concept();
            $c->identifier         = $concept;
            $c->status             = 'approved';
            $c->source             = 'test';
            $c->created            = 'today';
            $c->creator            = 'me';
            $c->creationIdentifier = 'nothing';
            $c->save();
        }
        $w   = Doctrine_Core::getTable('Concept')->findOneByIdentifier('Woodworking');
        $sw1 = Doctrine_Core::getTable('Concept')->findOneByIdentifier('Subwoodworking 1');
        $sw2 = Doctrine_Core::getTable('Concept')->findOneByIdentifier('Subwoodworking 2');
        $m   = Doctrine_Core::getTable('Concept')->findOneByIdentifier('Metalworking');
        $sm1 = Doctrine_Core::getTable('Concept')->findOneByIdentifier('Submetalworking 1');
        $sm2 = Doctrine_Core::getTable('Concept')->findOneByIdentifier('Submetalworking 2');
        $d   = Doctrine_Core::getTable('Concept')->findOneByIdentifier('drilled');
        $wd  = Doctrine_Core::getTable('Concept')->findOneByIdentifier('welded');
        $t   = Doctrine_Core::getTable('Concept')->findOneByIdentifier('turned');
        $s   = Doctrine_Core::getTable('Concept')->findOneByIdentifier('Surfaceworking');

        $w->narrowerConcepts[] = $sw1;
        $w->narrowerConcepts[] = $sw2;
        $w->save();

        $sw1->narrowerConcepts[] = $s;
        $sw1->narrowerConcepts[] = $d;
        $sw1->narrowerConcepts[] = $t;
        $sw1->save();

        $sw2->narrowerConcepts[] = $d;
        $sw2->save();

        $m->narrowerConcepts[] = $sm1;
        $m->narrowerConcepts[] = $sm2;
        $m->save();

        $sm1->narrowerConcepts[] = $wd;
        $sm1->narrowerConcepts[] = $s;
        $sm1->save();

        $sm2->narrowerConcepts[] = $t;
        $sm2->save();

        $s->narrowerConcepts[] = $t;
        $s->narrowerConcepts[] = $d;
        $s->save();
    }

    /**
     * this test will fail ...
     */
    public function testFAIL()
    {
        $this->resetData();

        ConceptRelation::showAllRelations();
        //lets count all relations
        $relCount = ConceptRelation::countAll();

        $oRecord             = Doctrine_Core::getTable('Concept')->findOneByIdentifier('Surfaceworking');
        $oRecord->identifier = 'MySurfaceworking';
        $oRecord->save();

        ConceptRelation::showAllRelations();

        // we did not change any relations, so we assume this test to be passed
        $this->assertEqual(ConceptRelation::countAll(), $relCount);
        // -> where do the additional relations come from ???
    }

    /*
     * ... while this test is ok (since we dont save anything)
     */
    public function testOK()
    {
        $this->resetData();

        ConceptRelation::showAllRelations();
        //lets count all relations
        $relCount = ConceptRelation::countAll();

        $oRecord             = Doctrine_Core::getTable('Concept')->findOneByIdentifier('Surfaceworking');
        $oRecord->identifier = 'MySurfaceworking';
        // $oRecord->save();  --> only this line differs !!!

        ConceptRelation::showAllRelations();

        // we did not change any relations, so we assume this test to be passed
        $this->assertEqual(ConceptRelation::countAll(), $relCount);
    }
}








/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class BaseConcept extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('concepts');
        $this->hasColumn('id', 'integer', 4, array('primary' => true, 'autoincrement' => true, 'type' => 'integer', 'length' => '4'));
        $this->hasColumn('vok_id as vokId', 'integer', 4, array('type' => 'integer', 'length' => '4'));
        $this->hasColumn('identifier', 'string', 255, array('notnull' => true, 'type' => 'string', 'length' => '255'));
        $this->hasColumn('status', 'string', 20, array('notnull' => true, 'type' => 'string', 'length' => '20'));
        $this->hasColumn('source', 'string', 255, array('notnull' => true, 'type' => 'string', 'length' => '255'));
        $this->hasColumn('created_on as created', 'string', 255, array('notnull' => true, 'type' => 'string', 'length' => '255'));
        $this->hasColumn('creator', 'string', 255, array('notnull' => true, 'type' => 'string', 'length' => '255'));
        $this->hasColumn('creation_identifier as creationIdentifier', 'string', 255, array('notnull' => true, 'type' => 'string', 'length' => '255'));

        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        $this->hasMany('Concept as broaderConcepts', array('refClass'          => 'ConceptRelation',
                                                       'local'                 => 'concept_id',
                                                       'foreign'               => 'parent_concept_id',
                                                       'refClassRelationAlias' => 'broaderLinks'));


        $this->hasMany('Concept as narrowerConcepts', array('refClass'          => 'ConceptRelation',
                                                        'local'                 => 'parent_concept_id',
                                                        'foreign'               => 'concept_id',
                                                        'refClassRelationAlias' => 'narrowerLinks'));
    }
}

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class BaseConceptRelation extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('concepts_x_concepts');
        $this->hasColumn('concept_id as conceptId', 'integer', 4, array('type' => 'integer', 'notnull' => true, 'length' => '4', 'primary' => true));
        $this->hasColumn('parent_concept_id as conceptIdParent', 'integer', 4, array('type' => 'integer', 'notnull' => true, 'length' => '4', 'primary' => true));

        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        $this->hasOne('Concept as concept', array('local' => 'concept_id',
                                              'foreign'   => 'id'));

        $this->hasOne('Concept as broaderConcept', array('local' => 'parent_concept_id',
                                             'foreign'           => 'id'));
    }
}


/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Concept extends BaseConcept
{
}

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class ConceptRelation extends BaseConceptRelation
{
    public static function showAllRelations()
    {
        /*$relations = Doctrine_Core::getTable("ConceptRelation")->findAll();
        foreach ($relations as $relation) {
          echo $relation->broaderConcept->identifier."(".$relation->conceptIdParent.")->".$relation->concept->identifier."(".$relation->conceptId.")\n<br/>";
        }
        echo "\n\n<br/><br/>";*/
    }

    public static function countAll()
    {
        return Doctrine_Core::getTable('ConceptRelation')->count();
    }
}
