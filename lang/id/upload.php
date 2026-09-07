<?php

return [

    'title' => 'Upload Gambar & Queue Event',
    'kicker' => 'UPLOAD PIPELINE',
    'heading' => 'Upload Gambar & Push Event',
    'subheading' => 'Upload gambar ke storage lokal Laravel; sistem otomatis publish metadata ke queue RabbitMQ (:queue) untuk dikonsumsi Go Consumer.',
    'validation_failed' => 'Terjadi kesalahan validasi:',
    'form_heading' => 'Form Upload',
    'choose_image' => 'Pilih Gambar',
    'dropzone' => 'Klik untuk memilih file gambar',
    'dropzone_hint' => 'PNG, JPG, JPEG, WEBP, GIF (Maks. 5MB)',
    'preview' => 'Preview Gambar:',
    'submit' => 'Upload & Publish ke RabbitMQ',
    'result_heading' => 'Result Upload Terakhir',
    'original_name' => 'Nama File Asli:',
    'stored_name' => 'Nama simpan:',
    'size' => 'Ukuran:',
    'mq_status' => 'Status RabbitMQ:',
    'config_heading' => 'Status Konfigurasi',
    'rabbitmq_host' => 'RabbitMQ Host',
    'rabbitmq_queue' => 'RabbitMQ Queue',
    'grafana' => 'Grafana Dashboard',
    'rabbitmq_manager' => 'RabbitMQ Manager',
    'uploaded' => 'Gambar berhasil di-upload!',
    'uploaded_mq_failed' => ' (Catatan: Event RabbitMQ gagal dikirim / RabbitMQ offline)',
    'mq_sent' => 'Terkirim ke RabbitMQ',
    'mq_failed' => 'Gagal terkirim ke RabbitMQ',

];
