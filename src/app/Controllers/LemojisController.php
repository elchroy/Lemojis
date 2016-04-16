<?php

namespace Elchroy\Lemojis\Controllers;

use Elchroy\Lemojis\Controllers\Traits\ReturnJsonTrait as ReturnJson;
use Elchroy\Lemojis\Models\LemojisModel as Emoji;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class LemojisController
{
    /*
     * Use the trait for the JSON responses.
     */
    use ReturnJson;

    /**
     * Get all the emojis inside the databsae.
     *
     * @param  A slim request object.
     * @param  A slim repsonse object.
     * @param  The slim argument paramter, to handle all the parameters that are passed with the request.
     *
     * @return The returned JSON response with all the emojis after being fetched from the database.
     *             If none is found, then a 404 message is returned.
     */
    public function getEmojis($request, $response, $args)
    {
        $emojis = Emoji::all();
        if ($emojis == null || count($emojis) < 1) {
            return $this->returnJSONResponse($response, 'There are no emojis loaded. Register and Login to create an emoji.', 404);
        }
        foreach ($emojis as $emoji) {
            $emoji->keywords = json_decode($emoji->keywords);
        }

        return $this->returnJSONResponse($response, 'OK', 200, $emojis);
    }

    /**
     * Get on emojis the database from the database given the id of the emoji.
     *
     * @param  A slim request object.
     * @param  A slim repsonse object.
     * @param  The slim argument paramter, to handle all the parameters that are passed with the request.
     *
     * @return The returned JSON response with all the emojis after being fetched from the database.
     *             If the emoji isnot found, then a 404 message is returned.
     */
    public function getEmoji($request, $response, $args)
    {
        $id = $args['id'];
        $emoji = $this->findEmoji($id);
        if (!$emoji) {
            return $this->returnJSONResponse($response, 'Cannot find the emoji', 404);
        }
        $emoji->keywords = json_decode($emoji->keywords);

        return $this->returnJSONResponse($response, 'OK', 200, $emoji);
        // return json_encode($emoji);
    }

    /**
     * Create an emoji and add to the database
     * Only authenticated users can create an emoji.
     *
     * @param  A slim request object.
     * @param  A slim repsonse object.
     * @param  The slim argument paramter, to handle all the parameters that are passed with the request.
     *
     * @return The returned 201 status and message that the emoji has been created.
     */
    public function createEmoji($request, $response, $args)
    {
        $params = $request->getParsedBody();
        $name = $params['name'];
        $chars = $params['chars'];
        $keywords = $params['keywords'];
        $category = $params['category'];
        $storeInfo = json_decode($request->getAttribute('StoreToken'));
        $created_by = $storeInfo[1];
        Emoji::create([
            'name'          => $name,
            'chars'         => $chars,
            'keywords'      => $this->prepareKeywordsArray($keywords),
            'category'      => $category,
            'date_created'  => $this->getDate(),
            'date_modified' => $this->getDate(),
            'created_by'    => $created_by,
        ]);

        return $this->returnJSONResponse($response, 'The new emoji has been created successfully.', 201);
    }

    /**
     * Private function to prepare the keywords as a JSON encoded array.
     * Only authenticated users can create an emoji.
     *
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
        $result = json_encode(array_values($words));

        return $result;
    }

    /**
     * Update an emoji given the ID of the emoji. Only authenticated users can create an emoji.
     * The update information refers to all the properties of the emoji.
     *
     * @param  A slim request object.
     * @param  A slim repsonse object.
     * @param  The slim argument paramter, to handle all the parameters that are passed with the request.
     *
     * @return A 200 status message that the update has been performed.
     *           If the emoji with the given ID cannot be found, then 404 response message is returned.
     */
    public function updateEmoji($request, $response, $args)
    {
        $id = $args['id'];
        $emoji = $this->findEmoji($id);
        if (!$emoji) {
            return $this->returnJSONResponse($response, 'Cannot find the emoji to update.', 404);
        }
        if (!($this->isUsersEmoji($request, $emoji))) {
            return $this->returnJSONResponse($response, 'You can only update your own emoji.', 401);
        }

        $emoji->date_modified = $this->getDate();
        $emoji->update($request->getParsedBody());

        return $this->returnJSONResponse($response, 'The emoji has been updated successfully.', 200);
    }

    /**
     * Check if the user is the owner of the requested emoji.
     *
     * @param  The slim request object.
     * @param  The emoji to be checked against the user.
     *
     * @return TRUE if the user is the owner of the emoji. FALSE otherwise.
     */
    private function isUsersEmoji($request, $emoji)
    {
        $username = $request->getAttribute('LoggedUser');
        $owner = $emoji->created_by;
        return $owner == $username;
    }

    /**
     * Update an emoji in the database, given the ID. Only authenticated users can create an emoji
     * The update information can be only for some properties of the database.
     *
     * @param  A slim request object.
     * @param  A slim repsonse object.
     * @param  The slim argument parameter, to handle all the parameters that are passed with the request.
     *
     * @return The returned 201 status and message that the partial update has been performed.
     *             If the emoji cannot be found, then a 200 code response message is returned.
     */
    public function updateEmojiPart($request, $response, $args)
    {
        $id = $args['id'];
        $emoji = $this->findEmoji($id);
        if (!$emoji) {
            return $this->returnJSONResponse($response, 'Cannot find the emoji to update.', 404);
        }
        if (!($this->isUsersEmoji($request, $emoji))) {
            return $this->returnJSONResponse($response, 'You can only update your own emoji.', 401);
        }

        $emoji->date_modified = $this->getDate();
        $emoji->update($request->getParsedBody());

        return $this->returnJSONResponse($response, 'The emoji has been updated successfully.', 200);
    }

    /**
     * Delete an emoji from the database, given the ID. Only authenticated users can delete an emoji.
     *
     * @param  A slim request object.
     * @param  A slim repsonse object.
     * @param  The slim argument parameter, to handle all the parameters that are passed with the request.
     *
     * @return The returned 201 status and message that the emoji has been deleted.
     *             If the emoji cannot be found, then a 200 code response message is returned.
     */
    public function deleteEmoji($request, $response, $args)
    {
        $id = $args['id'];
        $emoji = $this->findEmoji($id);
        if (!$emoji) {
            return $this->returnJSONResponse($response, 'Cannot find the emoji to delete.', 404);
        }
        if (!($this->isUsersEmoji($request, $emoji))) {
            return $this->returnJSONResponse($response, 'You can only update your own emoji.', 401);
        }

        Emoji::destroy($id);

        return $this->returnJSONResponse($response, 'The emoji has been deleted.', 200);
    }

    /**
     * Private function to find an emoji in the database given an ID.
     *
     * @param The ID of the emoji to find.
     *
     * @return The returned emoji if it found. Otherwise, FALSE is returned.
     */
    private function findemoji($id)
    {
        $em = Emoji::find($id);

        return $em ? Emoji::find($id) : false;
    }

    /**
     * Return the current date.
     *
     * @return A new date object.
     */
    private function getDate()
    {
        return date('Y-m-d H:m:s');
    }
}
