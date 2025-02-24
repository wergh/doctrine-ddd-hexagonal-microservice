<?php

namespace App\Infrastructure\Provider;

use RuntimeException;

abstract class ApiProvider
{
    /**
     * The endpoint URL for fetching doctors.
     */
    protected string $endpoint;

    /**
     * The username for API authentication.
     */
    protected string $username;

    /**
     * The password for API authentication.
     */
    protected string $password;

    /**
     * Constructor to initialize API credentials from environment variables.
     */
    public function __construct()
    {
        $this->endpoint = $_ENV['API_ENDPOINT'] ?? 'http://localhost:2137/api/doctors';
        $this->username = $_ENV['API_USERNAME'] ?? '';
        $this->password = $_ENV['API_PASSWORD'] ?? '';
    }

    /**
     * Fetches data from a given URL with basic authentication.
     *
     * @param string $url The URL to fetch data from.
     * @return string The response body.
     * @throws RuntimeException If an error occurs while fetching the data.
     */
    protected function fetchData(string $url): string
    {
        $auth = base64_encode($this->username . ':' . $this->password);

        $context = stream_context_create([
            'http' => [
                'header' => 'Authorization: Basic ' . $auth,
            ]
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            throw new RuntimeException("Error fetching data from $url");
        }

        return $response;
    }
}
