<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQService
{
    /**
     * Publish image metadata payload to RabbitMQ.
     *
     * @param  array<string, mixed>  $payload
     */
    public function publishImageUpload(array $payload): bool
    {
        $host = config('rabbitmq.host');
        $port = config('rabbitmq.port');
        $user = config('rabbitmq.user');
        $password = config('rabbitmq.password');
        $queue = config('rabbitmq.queue');

        try {
            $connection = new AMQPStreamConnection($host, $port, $user, $password, '/', false, 'AMQPLAIN', null, 'en_US', 3.0, 3.0);
            $channel = $connection->channel();

            // Declare queue: name, passive, durable, exclusive, auto_delete
            $channel->queue_declare($queue, false, true, false, false);

            $msgBody = json_encode($payload, JSON_THROW_ON_ERROR);

            $msg = new AMQPMessage($msgBody, [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            ]);

            $channel->basic_publish($msg, '', $queue);

            $channel->close();
            $connection->close();

            Log::info('Image upload message published to RabbitMQ', ['queue' => $queue, 'filename' => $payload['filename'] ?? null]);

            return true;
        } catch (Exception $e) {
            Log::warning('Failed to publish message to RabbitMQ', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return false;
        }
    }
}
