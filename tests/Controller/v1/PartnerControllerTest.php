<?php

namespace App\Tests\Controller\v1;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response; //https://api.symfony.com/4.1/Symfony/Component/HttpFoundation/Response.html

class PartnerControllerTest extends WebTestCase
{
    private $client = null;

    protected function setUp()
	{
        $this->client = $this->createClient(['environment' => 'test']);
        $this->client->disableReboot();
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->em->beginTransaction();
    }

    protected function tearDown()
    {
        $this->em->rollback();
    }

    protected function createAuthenticatedClient()
    {
        $client = $this->client;
        $post = ['_username'=>'admin','_password'=>'pa$$w0rd'];
        $client->request('POST', '/api/v1/tokens', $post);
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        $client = $this->client;
        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $content['token']));

        return $client;
    }



    /**
     * @dataProvider provide_partners_HTTP_UNAUTHORIZED
     */
    public function test_partners_HTTP_UNAUTHORIZED($method = null, $url = null, $post = [])
    {
        $client = $this->client;
        $client->request($method, $url, $post);
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('content-type'));
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $content['code']);
    }
    public function provide_partners_HTTP_UNAUTHORIZED()
    {
        return [
            ['GET',     '/api/v1/partners',     []],
            ['GET',     '/api/v1/partners/1',   []],
            ['POST',    '/api/v1/partners',     []],
            ['PATCH',   '/api/v1/partners/1',   []],
            ['DELETE',  '/api/v1/partners/1',   []],
        ];
    }



// AUTHENTICATED



    // GET


    
    public function test_GET_partners_HTTP_OK()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/v1/partners');
        $response = $client->getResponse();
        $content = json_decode($response->getcontent(), true);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('content-type'));
        $this->assertEquals('4', count($content));
    }

    /**
     * @dataProvider provide_partners_order_HTTP_OK
     */
    public function test_partners_order_HTTP_OK($url = null, $code = null)
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', $url);
        $response = $client->getResponse();
        $content = json_decode($response->getcontent(), true);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('content-type'));
        $this->assertEquals($code, $content[0]['code']);
    }
    public function provide_partners_order_HTTP_OK()
    {
        return [
            ['/api/v1/partners?sort_by=name&sort_dir=ASC', 'AAAAAA'],
            ['/api/v1/partners?sort_by=name&sort_dir=DESC', 'DDDDDD'],
            ['/api/v1/partners?sort_by=name&sort_dir=ASC&offset=0&limit=1', 'AAAAAA'],
            ['/api/v1/partners?sort_by=name&sort_dir=ASC&offset=1&limit=1', 'BBBBBB'],
            ['/api/v1/partners?sort_by=name&sort_dir=ASC&offset=2&limit=1', 'CCCCCC'],
            ['/api/v1/partners?sort_by=name&sort_dir=ASC&offset=3&limit=1', 'DDDDDD'],
            ['/api/v1/partners?sort_by=name&sort_dir=DESC&offset=0&limit=1', 'DDDDDD'],
            ['/api/v1/partners?sort_by=name&sort_dir=DESC&offset=1&limit=1', 'CCCCCC'],
            ['/api/v1/partners?sort_by=name&sort_dir=DESC&offset=2&limit=1', 'BBBBBB'],
            ['/api/v1/partners?sort_by=name&sort_dir=DESC&offset=3&limit=1', 'AAAAAA'],
        ];
    }

    /**
     * @dataProvider provide_partners_limit_HTTP_OK
     */
    public function test_partners_limit_HTTP_OK($url = null, $items = null)
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', $url);
        $response = $client->getResponse();
        $content = json_decode($response->getcontent(), true);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('content-type'));
        $this->assertEquals($items, count($content));
    }
    public function provide_partners_limit_HTTP_OK()
    {
        return [
            ['/api/v1/partners?offset=0&limit=0', 0],
            ['/api/v1/partners?offset=0&limit=1', 1],
            ['/api/v1/partners?offset=0&limit=2', 2],
            ['/api/v1/partners?offset=0&limit=3', 3],
            ['/api/v1/partners?offset=0&limit=4', 4],

            ['/api/v1/partners?offset=1&limit=0', 0],
            ['/api/v1/partners?offset=1&limit=1', 1],
            ['/api/v1/partners?offset=1&limit=2', 2],
            ['/api/v1/partners?offset=1&limit=3', 3],
            ['/api/v1/partners?offset=1&limit=4', 3],

            ['/api/v1/partners?offset=2&limit=0', 0],
            ['/api/v1/partners?offset=2&limit=1', 1],
            ['/api/v1/partners?offset=2&limit=2', 2],
            ['/api/v1/partners?offset=2&limit=3', 2],
            ['/api/v1/partners?offset=2&limit=4', 2],

            ['/api/v1/partners?offset=3&limit=0', 0],
            ['/api/v1/partners?offset=3&limit=1', 1],
            ['/api/v1/partners?offset=3&limit=2', 1],
            ['/api/v1/partners?offset=3&limit=3', 1],
            ['/api/v1/partners?offset=3&limit=4', 1],

            ['/api/v1/partners?offset=4&limit=0', 0],
            ['/api/v1/partners?offset=4&limit=1', 0],
            ['/api/v1/partners?offset=4&limit=2', 0],
            ['/api/v1/partners?offset=4&limit=3', 0],
            ['/api/v1/partners?offset=4&limit=4', 0],
        ];
    }

    public function test_GET_partners_id_HTTP_OK()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/v1/partners/1');
        $response = $client->getResponse();
        $content = json_decode($response->getcontent(), true);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('content-type'));
        $this->assertEquals('1', $content['id']);
    }

    public function test_GET_partners_id_HTTP_BAD_REQUEST()
    {
        $client = $this->createAuthenticatedClient();
        
        $client->request('GET', '/api/v1/partners/0');
        $response = $client->getResponse();
        $content = json_decode($response->getcontent(), true);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('content-type'));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $content['code']);
    }
    


    // POST

    

    public function test_POST_partners_HTTP_CREATED()
    {
        $client = $this->createAuthenticatedClient();
        
        $post = ['name'=>'name','surname'=>'surname','email'=>'email@email.com','active'=>'1','role'=>'1'];
        $client->request('POST', '/api/v1/partners', $post);
        $response = $client->getResponse();
        $content = json_decode($response->getcontent(), true);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('content-type'));
        $this->assertEquals('5', count($content));   
    }

    /**
     * @dataProvider provide_POST_partners_HTTP_BAD_REQUEST
     */
    public function test_POST_partners_HTTP_BAD_REQUEST($post = null)
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/v1/partners', $post);
        $response = $client->getResponse();
        $content = json_decode($response->getcontent(), true);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('content-type'));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $content['code']);
    }
    public function provide_POST_partners_HTTP_BAD_REQUEST()
    {
        return [
            [[]],
            [[               'surname'=>'surname','email'=>'email','active'=>'1','role'=>'1']],
            [['name'=>'name'                     ,'email'=>'email','active'=>'1','role'=>'1']],
            [['name'=>'name','surname'=>'surname'                 ,'active'=>'1','role'=>'1']],
            [['name'=>'name','surname'=>'surname','email'=>'email'              ,'role'=>'1']],
            [['name'=>'name','surname'=>'surname','email'=>'email','active'=>'1'            ]],
            
            [['name'=>'','surname'=>'surname','email'=>'email','active'=>'1','role'=>'1']],                 // name
            [['name'=>'name','surname'=>'','email'=>'email','active'=>'1','role'=>'1']],                    // surname
            [['name'=>'name','surname'=>'surname','email'=>'email','active'=>'1','role'=>'1']],             // email
            [['name'=>'name','surname'=>'surname','email'=>'e@e.e','active'=>'a','role'=>'1']],             // active
            [['name'=>'name','surname'=>'surname','email'=>'e@e.e','active'=>'1','role'=>'a']],             // role
            [['name'=>'name','surname'=>'surname','email'=>'email1@email.com','active'=>'1','role'=>'1']],  // email exist
        ];
    }



    // PATCH



    /**
     * @dataProvider provide_PATCH_partners_HTTP_CREATED
     */
    public function test_PATCH_partners_HTTP_CREATED($post = null)
    {
        $client = $this->createAuthenticatedClient();

        $client->request('PATCH', '/api/v1/partners/1', $post);
        $response = $client->getResponse();
        $content = json_decode($response->getcontent(), true);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('content-type'));        
        $this->assertEquals($post[key($post)], $content[key($post)]);  
    }
    public function provide_PATCH_partners_HTTP_CREATED()
    {
        return [
            [['name'=>'nuevo']],
            [['surname'=>'nuevo']],
            [['email'=>'nuevo@email.com']],
            //[['password'=>'nuevo']],
            [['active'=>'0']],
            [['role'=>'0']],
        ];
    }

    /**
     * @dataProvider provide_PATCH_partners_HTTP_BAD_REQUEST
     */
    public function test_PATCH_partners_HTTP_BAD_REQUEST($post = null)
    {
        $client = $this->createAuthenticatedClient();

        $client->request('PATCH', '/api/v1/partners/1', $post);
        $response = $client->getResponse();
        $content = json_decode($response->getcontent(), true);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('content-type'));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $content['code']);
    }
    public function provide_PATCH_partners_HTTP_BAD_REQUEST()
    {
        return [
            [[]],
            [['name'=>'']],
            [['surname'=>'']],
            [['email'=>'email no válido']],
            [['email'=>'email1@email.com']],
            [['password'=>'']],
            [['active'=>'a']],
            [['role'=>'a']],
        ];
    }



    // DELETE



    public function test_DELETE_partners_HTTP_ACCEPTED()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('DELETE', '/api/v1/partners/3');
        $response = $client->getResponse();
        $content = json_decode($response->getcontent(), true);

        $this->assertEquals(Response::HTTP_ACCEPTED, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('content-type'));
    }
    
    public function test_DELETE_partners_HTTP_PARTIAL_CONTENT()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('DELETE', '/api/v1/partners/1');
        $response = $client->getResponse();
        $content = json_decode($response->getcontent(), true);

        $this->assertEquals(Response::HTTP_PARTIAL_CONTENT, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('content-type'));
        $this->assertEquals(Response::HTTP_PARTIAL_CONTENT, $content['code']);
    }

    public function test_DELETE_partners_HTTP_BAD_REQUEST()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('DELETE', '/api/v1/partners/0');
        $response = $client->getResponse();
        $content = json_decode($response->getcontent(), true);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('content-type'));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $content['code']);
    }

}