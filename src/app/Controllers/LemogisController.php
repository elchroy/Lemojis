<?php

namespace Elchroy\Lemogis\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Elchroy\Lemogis\Models\LemogisModel as Emogi;
use Elchroy\Lemogis\Controllers\Traits\ReturnJsonTrait as ReturnJson;

class LemogisController
{
    use ReturnJson;

    public function getEmogis($request, $response, $args)
    {
        $emogis = Emogi::all();
        if ($emogis == null || count($emogis) < 1) {
            return $this->returnJSONResponse($response, 'There are no emogis loaded. Register and Login to create an emogi.', 404);
        }
        return $this->returnJSONResponse($response, 'OK', 200, $emogis);
    }

    public function getEmogi($request, $response, $args)
    {
        $id = $args['id'];
        $emogi = $this->findEmogi($id);
        if (!$emogi) {
            return $this->returnJSONResponse($response, 'Cannot find the emoji', 404);
        }

        return $this->returnJSONResponse($response, 'OK', 200, $emogi);
        // return json_encode($emogi);
    }

    public function createEmogi($request, $response, $args)
    {
        $params = $request->getParsedBody();
        $name = $params['name'];
        $chars = $params['chars'];
        $keywords = $params['keywords'];
        $category = $params['category'];
        $storeInfo = json_decode($request->getAttribute('StoreToken'));
        $created_by = $storeInfo[1];
        Emogi::create([
            'name' => $name,
            'chars' => $chars,
            'keywords' => $this->prepareKeywordsArray($keywords),
            'category' => $category,
            'date_created' => $this->getDate(),
            'date_modified' => $this->getDate(),
            'created_by' => $created_by,
        ]);
        return $this->returnJSONResponse($response, 'The new emoji has been created successfully.', 201);
    }

    private function prepareKeywordsArray($words)
    {
        $words = strtolower($words);
        $words = preg_replace('/[\W]+/', ' ', $words);
        $words = trim($words);
        $words = explode(' ', $words);
        $words = array_unique($words);
        $result =  json_encode(array_values($words));
        return $result;
    }

    public function updateEmogi($request, $response, $args)
    {
        $id = $args['id'];
        $emogi = $this->findEmogi($id);
        if (!$emogi) {
            return $this->returnJSONResponse($response, 'Cannot find the emoji to update.', 404);
        }
        $emogi->date_modified = $this->getDate();
        $emogi->update($request->getParsedBody());
        return $this->returnJSONResponse($response, 'The Emogi has been updated successfully.', 200);
    }

    public function updateEmogiPart($request, $response, $args)
    {
        $id = $args['id'];
        $emogi = $this->findEmogi($id);
        if (!$emogi) {
            return $this->returnJSONResponse($response, 'Cannot find the emoji to update.', 404);
        }
        $emogi->date_modified = $this->getDate();
        $emogi->update($request->getParsedBody());
        return $this->returnJSONResponse($response, 'The Emogi has been updated successfully.', 200);
    }

    public function deleteEmogi($request, $response, $args)
    {
        $id = $args['id'];
        $emogi = $this->findEmogi($id);
        if (!$emogi) {
            return $this->returnJSONResponse($response, 'Cannot find the emoji to delete.', 404);
        }
        Emogi::destroy($id);

        return $this->returnJSONResponse($response, 'The Emogi has been deleted.', 200);
    }

    private function findEmogi($id)
    {
        $em = Emogi::find($id);
        return $em ? Emogi::find($id) : false;
    }

    private function getDate()
    {
        return date("Y-m-d H:m:s");
    }

}