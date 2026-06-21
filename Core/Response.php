<?php
namespace Core;

class Response {
    private Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger('system.log');
    }

    public function json(
        array $data,
        int $status = 200
    ): void {
        http_response_code($status);

        header(
            'Content-Type: application/json; charset=UTF-8'
        );

        $json = json_encode($data);

        if ($json === false) {
            http_response_code(500);
            $this->logger->error('JSON response encoding failed: ' . json_last_error_msg());

            echo json_encode([
                'message' => 'Internal server error.'
            ]);

            return;
        }

        echo $json;
    }
}
