<?php

namespace Elchroy\Lemogis\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Elchroy\Lemogis\Models\LemogisModel as Emogi;
use Elchroy\Lemogis\Controllers\Traits\ReturnJsonTrait as ReturnJson;

class LemogisController
{
    /**
     * Use the trait for the JSON responses.
     */
    use ReturnJson;

    /**
     * Get all the emogis inside the databsae.
     * @param  A slim request object.
     * @param  A slim repsonse object.
     * @param  The slim argument paramter, to handle all the parameters that are passed with the request.
     *
     * @return The returned JSON response with all the emogis after being fetched from the database.
     * If none is found, then a 404 message is returned.
     */
    public function getEmogis($request, $response, $args)
    {
        $emogis = Emogi::all();
        if ($emogis == null || count($emogis) < 1) {
            return $this->returnJSONResponse($response, 'There are no emogis loaded. Register and Login to create an emogi.', 404);
        }
        return $this->returnJSONResponse($response, 'OK', 200, $emogis);
    }

    /**
     * Get on emogis the database from the database given the id of the emogi.
     * @param  A slim request object.
     * @param  A slim repsonse object.
     * @param  The slim argument paramter, to handle all the parameters that are passed with the request.
     *
     * @return The returned JSON response with all the emogis after being fetched from the database.
     * If the emogi isnot found, then a 404 message is returned.
     */
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

    /**
     * Create an emogi and add to the database
     * Only authenticated users can create an emogi
     * @param  A slim request object.
     * @param  A slim repsonse object.
     * @param  The slim argument paramter, to handle all the parameters that are passed with the request.
     *
     * @return The returned 201 status and message that the emogi has been created.
     */
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

    /**
     * Private function to prepare the keywords as a JSON encoded array.
     * Only authenticated users can create an emogi
     * @param  The words as string to be converted into an array.
     *
     * @return The return JSON encoded string after the given words string has been converted to an array.
     */
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

    /**
     * Update an emogi given the ID of the emogi. Only authenticated users can create an emogi.
     * The update information refers to all the properties of the emogi.
     * @param  A slim request object.
     * @param  A slim repsonse object.
     * @param  The slim argument paramter, to handle all the parameters that are passed with the request.
     *
     * @return A 200 status message that the update has been performed.
     * If the emogi with the given ID cannot be found, then 404 response message is returned.
     */
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

    /**
     * Update an emogi in the database, given the ID. Only authenticated users can create an emogi
     * The update information can be only for some properties of the database.
     * @param  A slim request object.
     * @param  A slim repsonse object.
     * @param  The slim argument parameter, to handle all the parameters that are passed with the request.
     *
     * @return The returned 201 status and message that the partial update has been performed.
     * If the emogi cannot be found, then a 200 code response message is returned.
     */
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

    /**
     * Delete an emogi from the database, given the ID. Only authenticated users can delete an emogi
     * @param  A slim request object.
     * @param  A slim repsonse object.
     * @param  The slim argument parameter, to handle all the parameters that are passed with the request.
     *
     * @return The returned 201 status and message that the emogi has been deleted.
     * If the emogi cannot be found, then a 200 code response message is returned.
     */
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

    /**
     * Private function to find an emogi in the database given an ID
     * @param The ID of the emogi to find.
     *
     * @return The returned emogi if it found. Otherwise, FALSE is returned.
     */
    private function findEmogi($id)
    {
        $em = Emogi::find($id);
        return $em ? Emogi::find($id) : false;
    }

    /**
     * Return the current date.
     * @return A new date object.
     */
    private function getDate()
    {
        return date("Y-m-d H:m:s");
    }

}