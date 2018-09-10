<?php
/**
 * Created by PhpStorm.
 * User: presley
 * Date: 10/09/2018
 */


namespace App\Tests;



use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CompaniesControllerTest extends WebTestCase
{
    public function testAdminGetCompaniesAll(){
        $client = static::createClient();
        $client->request(
            'GET',
            '/api/companies',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTH-TOKEN' => '5b7fd37c2995a9.89857955',
            ]
        );

        $response = $client->getResponse();
        $content =$response->getContent();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($content);

        $arrayContent = json_decode($content, true);
        $this->assertCount(10, $arrayContent);
    }

    public function testUserGetCompaniesAll(){
        $client = static::createClient();
        $client->request(
            'GET',
            '/api/companies',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTH-TOKEN' => '5b7fd37c29d9f1.52897532',
            ]
        );

        $response = $client->getResponse();
        $content =$response->getContent();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($content);

        $arrayContent = json_decode($content, true);

    }

    public function testAnonymousGetCompaniesAll(){
        $client = static::createClient();
        $client->request('GET','/api/companies');

        $response = $client->getResponse();
        $content =$response->getContent();

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertJson($content);

        $arrayContent = json_decode($content, true);

    }

    public function testAdminGetCompaniesOne(){
        $client = static::createClient();
        $client->request(
            'GET',
            '/api/companies/1',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTH-TOKEN' => '5b7fd37c2995a9.89857955',
            ]
        );

        $response = $client->getResponse();
        $content =$response->getContent();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($content);

        $arrayContent = json_decode($content, true);

    }

    public function testUserGetCompaniesOne(){
        $client = static::createClient();
        $client->request(
            'GET',
            '/api/companies/1',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTH-TOKEN' => '5b7fd37c29d9f1.52897532',
            ]
        );

        $response = $client->getResponse();
        $content =$response->getContent();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($content);

        $arrayContent = json_decode($content, true);

    }

    public function testAnonymousGetCompaniesOne(){
        $client = static::createClient();
        $client->request('GET','/api/companies/1');

        $response = $client->getResponse();
        $content =$response->getContent();

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertJson($content);

        $arrayContent = json_decode($content, true);

    }

    public function testAdminPostCompanies(){
        $data = [
            "name" => "Presley company",
            "slogan"=> "A l'écoute de nos client",
            "phoneNumber"=> "0601020304",
            "address"=> "50, avenue du laugier 93440 Dugny"
        ];

        $client = static::createClient();
        $client->request(
            'POST',
            '/api/companies',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'AUTH-TOKEN' => '5b7fd37c2995a9.89857955',
            ],
            json_encode($data)
        );

        $response = $client->getResponse();
        $content =$response->getContent();

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertJson($content);

        $arrayContent = json_decode($content, true);

    }

    public function testUserPostCompanies(){
        $data = [
            "name" => "Modif",
            "slogan"=> "slogan de test",
            "phoneNumber"=> "0701020304",
            "address"=> "40, Rue lecourbe 75015 Paris"
        ];

        $client = static::createClient();
        $client->request(
            'POST',
            '/api/companies',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'AUTH-TOKEN' => '5b7fd37c29d9f1.52897532',
            ],
            json_encode($data)
        );

        $response = $client->getResponse();
        $content =$response->getContent();

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertJson($content);

        $arrayContent = json_decode($content, true);
    }

    public function testAnonymousPostCompanies(){
        $data = [
            "name" => "ouais mais non",
            "slogan"=> "sa pass pas chez nous sa",
            "phoneNumber"=> "0102030405",
            "address"=> "jcp, jcp jcp cjp "
        ];

        $client = static::createClient();
        $client->request(
            'POST',
            '/api/companies',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($data)
        );

        $response = $client->getResponse();
        $content =$response->getContent();

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertJson($content);

        $client = static::createClient();
        $client->request(
            'GET',
            '/api/companies',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTH-TOKEN' => '5b7fd37c2995a9.89857955',
            ]
        );

        $response = $client->getResponse();
        $content =$response->getContent();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($content);

        $arrayContent = json_decode($content, true);
        $this->assertCount(13, $arrayContent);
    }

    public function testAdminPutCompanies(){
        $data = [
            "name" => "société général"
        ];

        $client = static::createClient();
        $client->request(
            'PUT',
            '/api/companies/2',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTH-TOKEN' => '5b7fd37c2995a9.89857955',
            ],
            json_encode($data)
        );

        $response = $client->getResponse();
        $content =$response->getContent();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($content);

        $arrayContent = json_decode($content, true);

    }

    public function testAdminDeleteCompanies(){
        $client = static::createClient();
        $client->request(
            'DELETE',
            '/api/companies/11',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTH-TOKEN' => '5b7fd37c2995a9.89857955',
            ]
        );

        $response = $client->getResponse();
        $content =$response->getContent();

        //Bug du test lors du renvoi de code 204 ==> envoi 200
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($content);
    }

    public function testUserDeleteAnotherCompanies()
    {
        //Test to delete another company
        $client = static::createClient();
        $client->request(
            'DELETE',
            '/api/companies/5',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTH-TOKEN' => '5b7fd37c29d9f1.52897532',
            ]
        );

        $response = $client->getResponse();
        $content = $response->getContent();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($content);
    }


    public function testUserDeleteHisCompanies(){
        //Test to delete his company
        $client = static::createClient();
        $client->request(
            'DELETE',
            '/api/companies/4',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTH-TOKEN' => '5b7fd37c29d9f1.48751390',
            ]
        );

        $response = $client->getResponse();
        $content =$response->getContent();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($content);

    }

    public function testAnonymousDeleteCompanies(){
        $client = static::createClient();
        $client->request('DELETE','/api/companies/13');

        $response = $client->getResponse();
        $content =$response->getContent();

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertJson($content);

    }
}