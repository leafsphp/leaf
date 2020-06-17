<?php

namespace Leaf\Wynter\Connection;

use Leaf\Wynter;
use Illuminate\Support\Fluent;
use Illuminate\Validation\ValidationException;

abstract class ConnectionHandler
{
    public function handle($payload)
    {
        $instance = app('livewire')->activate($payload['name'], $payload['id']);

        try {
            Wynter::hydrate($instance, $payload);

            $instance->hydrate();

            foreach ($payload['actionQueue'] as $action) {
                $this->processMessage($action['type'], $action['payload'], $instance);
            }
        } catch (ValidationException $e) {
            Wynter::dispatch('failed-validation', $e->validator);

            $errors = $e->validator->errors();
        }

        $dom = $instance->output($errors ?? null);

        $response = new Fluent([
            'id' => $payload['id'],
            'name' => $payload['name'],
            'dom' => $dom,
        ]);

        Wynter::dehydrate($instance, $response);

        return $response;
    }

    public function processMessage($type, $data, $instance)
    {
        switch ($type) {
            case 'callMethod':
                $instance->callMethod($data['method'], $data['params']);
                break;
            case 'fireEvent':
                $instance->fireEvent($data['event'], $data['params']);
                break;
            default:
                break;
        }
    }
}
