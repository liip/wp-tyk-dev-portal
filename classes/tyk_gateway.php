<?php

declare(strict_types=1);
/**
 * Tyk API adapter.
 */

/**
 * Class to handle interaction with Tyk gateway
 * Note: if you have multiple gateways, TYK_GATEWAY_URL should point to your load balancer.
 */
class Tyk_Gateway extends Tyk_Interaction
{
    /**
     * Send a get request to Tyk gateway.
     *
     * @param string $path
     * @param array  $args Query string args
     *
     * @return array
     *
     * @throws Exception When API sends invalid response
     */
    public function get($path, ?array $args = null)
    {
        // if gateway credentials are configured, setup
        $api_response = wp_remote_get($this->get_url_for_path($path, $args), array(
            'headers' => array(
                'x-tyk-authorization' => TYK_GATEWAY_SECRET,
            ),
        ));

        $response = $this->parse_response($api_response);
        if (is_object($response)) {
            return $response;
        }

        throw new Exception('Received invalid response from Gateway');
    }

    /**
     * Send a delete request to Tyk gateway.
     *
     * @param string $path
     *
     * @return stdClass
     *
     * @throws Exception when the gateway sends an invalid response
     */
    public function delete($path)
    {
        $url = $this->get_url_for_path($path);

        $api_response = wp_remote_request($url, array(
            'method' => 'DELETE',
            'headers' => array(
                'x-tyk-authorization' => TYK_GATEWAY_SECRET,
            )));

        $response = $this->parse_response($api_response);
        if (is_object($response)) {
            return $response;
        }

        throw new Exception('Received invalid response from API');
    }

    /**
     * Get absolute url to api endpoint for a path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function get_url_for_path($path, ?array $args = null)
    {
        // build query string out of args if they're set
        $qs = '';
        if (is_array($args)) {
            $qs = '?' . http_build_query($args);
        }

        return sprintf(
            '%s/%s%s',
            rtrim(TYK_GATEWAY_URL, '/'),
            ltrim($path, '/'),
            $qs,
        );
    }
}
