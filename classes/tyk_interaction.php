<?php

declare(strict_types=1);
/**
 * Tyk API adapter.
 */

/**
 * Abstraction for classes interacting with Tyk.
 */
abstract class Tyk_Interaction
{
    /**
     * Abstraction must define how an url is obtained for a path.
     *
     * @param string $path
     *
     * @return string
     */
    abstract protected function get_url_for_path($path, ?array $args = null);

    /**
     * Parse and analyse response.
     *
     * @param mixed $api_response
     *
     * @return mixed
     *
     * @throws Exception When API sends a non-200 response code
     */
    protected function parse_response($api_response)
    {
        // parse errors
        if ($api_response instanceof WP_Error) {
            $error_list = array();
            foreach ($api_response->errors as $name => $errors) {
                $error_list[] = is_array($errors)
                    ? join('. ', $errors)
                    : $errors;
            }
            if (count($error_list) > 0) {
                throw new Exception(sprintf('API error: %s', join('. ', $error_list)));
            }

            throw new Exception('An unknown error occured when connecting to API');
        }
        // parse response
        else {
            $response = json_decode(wp_remote_retrieve_body($api_response));
            $http_code = wp_remote_retrieve_response_code($api_response);
            $message = wp_remote_retrieve_response_message($api_response);
            if (200 != $http_code) {
                // see if we have more information
                if (is_object($response) && isset($response->Message)) {
                    $message .= sprintf(': %s', $response->Message);
                }

                throw new Exception($message);
            }
        }

        return $response;
    }
}
