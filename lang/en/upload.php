<?php

return [

    'title' => 'Image Upload & Queue Event',
    'kicker' => 'UPLOAD PIPELINE',
    'heading' => 'Upload Image & Push Event',
    'subheading' => 'Upload an image to Laravel local storage; the system automatically publishes the metadata to the RabbitMQ queue (:queue) for the Go consumer.',
    'validation_failed' => 'Validation errors occurred:',
    'form_heading' => 'Upload Form',
    'choose_image' => 'Choose an image',
    'dropzone' => 'Click to pick an image file',
    'dropzone_hint' => 'PNG, JPG, JPEG, WEBP, GIF (max. 5MB)',
    'preview' => 'Image preview:',
    'submit' => '🚀 Upload & publish to RabbitMQ',
    'result_heading' => 'Last Upload Result',
    'original_name' => 'Original file name:',
    'stored_name' => 'Stored name:',
    'size' => 'Size:',
    'mq_status' => 'RabbitMQ status:',
    'config_heading' => '⚙️ Configuration Status',
    'rabbitmq_host' => 'RabbitMQ Host',
    'rabbitmq_queue' => 'RabbitMQ Queue',
    'grafana' => 'Grafana Dashboard',
    'rabbitmq_manager' => 'RabbitMQ Manager',
    'uploaded' => 'Image uploaded successfully!',
    'uploaded_mq_failed' => ' (Note: the RabbitMQ event failed to send / RabbitMQ is offline)',
    'mq_sent' => 'Sent to RabbitMQ',
    'mq_failed' => 'Failed to send to RabbitMQ',

];
