<?php
namespace jrdev\MySQL\Tests;

class MainTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    private $MySQL;

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _before() // @codingStandardsIgnoreLine
    {
        $this->MySQL = new \jrdev\MySQL('127.0.0.1', 'root', '', 'travis_db');
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _after() // @codingStandardsIgnoreLine
    {
    }

    public function testQueryMethod()
    {
        $query = $this->MySQL->query('SELECT * FROM `posts`');

        $this->assertTrue($query instanceof \jrdev\MySQL\Result, $this->MySQL->error());
    }

    public function testSelectMethod()
    {
        // Testing select method
        $query = $this->MySQL->select('posts');

        $this->assertTrue($query instanceof \jrdev\MySQL\Result, $this->MySQL->error());

        $this->assertEquals(3, $query->num_rows);

        $num = 1;
        foreach ($query as $row) {
            $this->assertArrayHasKey('id', $row);
            $this->assertArrayHasKey('title', $row);
            $this->assertEquals($num, $row['id']);

            $num++;
        }

        // Testing order by
        $query = $this->MySQL->select('posts', null, null, 'id DESC');

        $this->assertTrue($query instanceof \jrdev\MySQL\Result, $this->MySQL->error());

        $this->assertEquals(3, $query->num_rows);

        $num = 3;
        foreach ($query as $row) {
            $this->assertEquals($num, $row['id']);

            $num--;
        }

        // Testing fields method 1
        $query = $this->MySQL->select('posts', 'id,title', null, null, 1);

        $this->assertTrue($query instanceof \jrdev\MySQL\Result, $this->MySQL->error());

        $this->assertEquals(1, $query->num_rows);

        // Testing fields method 2
        $query = $this->MySQL->select('posts', ['id', 'title'], null, null, 1);

        $this->assertTrue($query instanceof \jrdev\MySQL\Result, $this->MySQL->error());

        $this->assertEquals(1, $query->num_rows);

        // Testing where method 1
        $query = $this->MySQL->select('posts', null, 1, null);

        $this->assertTrue($query instanceof \jrdev\MySQL\Result, $this->MySQL->error());

        $this->assertEquals(1, $query->num_rows);

        // Testing where method 2
        $query = $this->MySQL->select('posts', null, ['id' => 1], null);

        $this->assertTrue($query instanceof \jrdev\MySQL\Result, $this->MySQL->error());

        $this->assertEquals(1, $query->num_rows);

        // Testing where method 3
        $query = $this->MySQL->select('posts', null, 'id=1', null);

        $this->assertTrue($query instanceof \jrdev\MySQL\Result, $this->MySQL->error());

        $this->assertEquals(1, $query->num_rows);
    }

    public function testInsertMethod()
    {
        $insertedId = $this->MySQL->insert('posts', [
            'title' => "New Post"
        ]);

        $this->assertInternalType('integer', $insertedId, $this->MySQL->error());
        $this->assertGreaterThan(0, $insertedId);
    }

    public function testUpdateMethod()
    {
        // Testing Update method 1
        $updated = $this->MySQL->update('posts', ['title' => 'Post 2 Updated v1'], ['id' => 2]);

        $this->assertInternalType('integer', $updated, $this->MySQL->error());
        $this->assertEquals(1, $updated);

        // Testing Update method 2
        $updated = $this->MySQL->update('posts', ['title' => 'Post 2 Updated v2'], 'id=2');

        $this->assertInternalType('integer', $updated, $this->MySQL->error());
        $this->assertEquals(1, $updated);

        // Testing Update method 3
        $updated = $this->MySQL->update('posts', ['title' => 'Post 2 Updated v3'], 2);

        $this->assertInternalType('integer', $updated, $this->MySQL->error());
        $this->assertEquals(1, $updated);
    }

    public function testDeleteMethod()
    {
        // Testing Delete method 1
        $deleted = $this->MySQL->delete('posts', ['id' => 1]);

        $this->assertInternalType('integer', $deleted, $this->MySQL->error());
        $this->assertEquals(1, $deleted);

        // Testing Delete method 2
        $deleted = $this->MySQL->delete('posts', 'id=2');

        $this->assertInternalType('integer', $deleted, $this->MySQL->error());
        $this->assertEquals(1, $deleted);

        // Testing Delete method 3
        $deleted = $this->MySQL->delete('posts', 3);

        $this->assertInternalType('integer', $deleted, $this->MySQL->error());
        $this->assertEquals(1, $deleted);
    }

    public function testTableClass()
    {
        $posts = new \jrdev\MySQL\Table($this->MySQL, 'posts');

        $this->assertTrue($posts instanceof \jrdev\MySQL\Table);

        // Testing insert method
        $insertedId = $posts->insert(['title' => 'Other Post']);

        $this->assertInternalType('integer', $insertedId, $this->MySQL->error());
        $this->assertGreaterThan(0, $insertedId);

        // Testing update method
        $updated = $posts->update(['title' => 'Other Post - Updated'], ['id' => $insertedId]);

        $this->assertInternalType('integer', $updated, $this->MySQL->error());
        $this->assertEquals(1, $updated);

        // Testing delete method
        $deleted = $posts->delete(['id' => $insertedId]);

        $this->assertInternalType('integer', $deleted, $this->MySQL->error());
        $this->assertEquals(1, $deleted);

        // Testing save method, doing insert
        $insertedId = $posts->save(['title' => 'One more']);

        $this->assertInternalType('integer', $insertedId, $this->MySQL->error());
        $this->assertGreaterThan(0, $insertedId);

        // Testing save method, doing update
        $insertedId2 = $posts->save(['id' => $insertedId, 'title' => 'The same']);

        $this->assertInternalType('integer', $insertedId2, $this->MySQL->error());
        $this->assertEquals($insertedId, $insertedId2);
    }
}
