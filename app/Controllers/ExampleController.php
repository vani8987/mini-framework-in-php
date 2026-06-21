<?php

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\Response;
use App\Models\Example;

class ExampleController extends Controller
{
    public function index(): void
    {
        $examples = new Example();
        $data = $examples->findAll(['id', 'title', 'created_at']);

        $response = new Response();
        $response->json([
            'data' => $data,
        ]);
    }

    public function store(): void
    {
        $request = new Request();
        $title = $request->getDataJson('title');
        $response = new Response();

        if (!is_string($title) || trim($title) === '') {
            $response->json([
                'message' => 'The title field is required.',
            ], 422);
            return;
        }

        $examples = new Example();
        $created = $examples->create(['title'], [trim($title)]);

        if (!$created) {
            $response->json([
                'message' => 'Unable to create the example.',
            ], 500);
            return;
        }

        $response->json([
            'message' => 'Example created.',
        ], 201);
    }
}
