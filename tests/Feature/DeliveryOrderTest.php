<?php

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\DeliveryOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

/**
 * @property Customer $customer
 * @property CustomerAddress $address
 */
beforeEach(function () {
    $this->customer = Customer::create([
        'name'  => 'John Doe',
        'phone' => '1234567890',
        'email' => 'john@example.com',
        'city'  => 'Metropolis',
    ]);

    $this->address = CustomerAddress::create([
        'customer_id' => $this->customer->id,
        'label'       => 'Home',
        'street'      => '123 Main St',
        'city'        => 'Metropolis',
        'is_default'  => true,
    ]);
});

test('can list delivery orders with eager-loaded customer', function () {
    $order = DeliveryOrder::create([
        'customer_id'         => $this->customer->id,
        'delivery_address_id' => $this->address->id,
        'transport_method'    => 'own_rider',
        'items'               => [['description' => 'Widget A', 'quantity' => 2]],
        'status'              => 'pending',
    ]);

    $response = $this->getJson('/api/delivery-orders');

    $response->assertStatus(200)
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $order->id)
        ->assertJsonPath('0.customer.name', 'John Doe');
});

test('can filter delivery orders by status', function () {
    DeliveryOrder::create([
        'customer_id'         => $this->customer->id,
        'delivery_address_id' => $this->address->id,
        'transport_method'    => 'own_rider',
        'items'               => [['description' => 'Item 1', 'quantity' => 1]],
        'status'              => 'pending',
    ]);

    DeliveryOrder::create([
        'customer_id'         => $this->customer->id,
        'delivery_address_id' => $this->address->id,
        'transport_method'    => 'own_rider',
        'items'               => [['description' => 'Item 2', 'quantity' => 1]],
        'status'              => 'delivered',
    ]);

    $response = $this->getJson('/api/delivery-orders?status=pending');
    $response->assertStatus(200)->assertJsonCount(1);
    $response->assertJsonPath('0.status', 'pending');

    $responseDelivered = $this->getJson('/api/delivery-orders?status=delivered');
    $responseDelivered->assertStatus(200)->assertJsonCount(1);
    $responseDelivered->assertJsonPath('0.status', 'delivered');
});

test('can create delivery order with own rider', function () {
    $payload = [
        'customer_id'         => $this->customer->id,
        'delivery_address_id' => $this->address->id,
        'transport_method'    => 'own_rider',
        'items'               => [
            ['description' => 'Item A', 'quantity' => 3],
            ['description' => 'Item B', 'quantity' => 1],
        ],
        'notes'               => 'Handle with care',
    ];

    $response = $this->postJson('/api/delivery-orders', $payload);

    $response->assertStatus(201)
        ->assertJsonPath('transport_method', 'own_rider')
        ->assertJsonPath('status', 'pending')
        ->assertJsonPath('customer.id', $this->customer->id)
        ->assertJsonPath('delivery_address.id', $this->address->id);

    $this->assertDatabaseHas('delivery_orders', [
        'customer_id'      => $this->customer->id,
        'transport_method' => 'own_rider',
        'status'           => 'pending',
    ]);
});

test('can create delivery order with courier', function () {
    $payload = [
        'customer_id'         => $this->customer->id,
        'delivery_address_id' => $this->address->id,
        'transport_method'    => 'courier',
        'courier_name'        => 'DHL Express',
        'tracking_number'     => 'TRK-998877',
        'items'               => [
            ['description' => 'Special Package', 'quantity' => 1],
        ],
    ];

    $response = $this->postJson('/api/delivery-orders', $payload);

    $response->assertStatus(201)
        ->assertJsonPath('transport_method', 'courier')
        ->assertJsonPath('courier_name', 'DHL Express')
        ->assertJsonPath('tracking_number', 'TRK-998877');

    $this->assertDatabaseHas('delivery_orders', [
        'customer_id'     => $this->customer->id,
        'courier_name'    => 'DHL Express',
        'tracking_number' => 'TRK-998877',
    ]);
});

test('fails validation when transport_method is courier but courier fields are missing', function () {
    $payload = [
        'customer_id'         => $this->customer->id,
        'delivery_address_id' => $this->address->id,
        'transport_method'    => 'courier',
        'items'               => [
            ['description' => 'Package', 'quantity' => 1],
        ],
    ];

    $response = $this->postJson('/api/delivery-orders', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['courier_name', 'tracking_number']);
});

test('fails validation when items is empty', function () {
    $payload = [
        'customer_id'         => $this->customer->id,
        'delivery_address_id' => $this->address->id,
        'transport_method'    => 'own_rider',
        'items'               => [],
    ];

    $response = $this->postJson('/api/delivery-orders', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['items']);
});

test('can show delivery order', function () {
    $order = DeliveryOrder::create([
        'customer_id'         => $this->customer->id,
        'delivery_address_id' => $this->address->id,
        'transport_method'    => 'own_rider',
        'items'               => [['description' => 'Item X', 'quantity' => 5]],
        'status'              => 'pending',
    ]);

    $response = $this->getJson("/api/delivery-orders/{$order->id}");

    $response->assertStatus(200)
        ->assertJsonPath('id', $order->id)
        ->assertJsonPath('customer.id', $this->customer->id)
        ->assertJsonPath('delivery_address.id', $this->address->id);
});

test('can delete delivery order', function () {
    $order = DeliveryOrder::create([
        'customer_id'         => $this->customer->id,
        'delivery_address_id' => $this->address->id,
        'transport_method'    => 'own_rider',
        'items'               => [['description' => 'Item', 'quantity' => 1]],
        'status'              => 'pending',
    ]);

    $response = $this->deleteJson("/api/delivery-orders/{$order->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('delivery_orders', ['id' => $order->id]);
});
