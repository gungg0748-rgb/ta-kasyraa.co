<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function userWithRole(string $role): User
{
    return User::factory()->create(['role' => $role, 'is_active' => true]);
}

function makeProduct(): Product
{
    return Product::create([
        'name'          => 'Kemeja Linen',
        'category_id'   => Category::create(['name' => 'Atasan'])->id,
        'unit_id'       => Unit::create(['name' => 'Pcs'])->id,
        'price'         => 150000,
        'reorder_level' => 5,
        'barcode'       => 'PRD-TEST01',
    ]);
}

// --- Kelola produk/varian: hanya admin & owner ---------------------------------

test('admin & owner bisa membuka form kelola produk', function (string $role) {
    $this->actingAs(userWithRole($role))->get('/products/create')->assertOk();
})->with(['admin', 'owner']);

test('gudang & kasir ditolak mengelola produk', function (string $role) {
    $this->actingAs(userWithRole($role))->get('/products/create')->assertForbidden();
})->with(['gudang', 'kasir']);

// --- Lihat katalog (referensi): admin, owner, gudang; kasir ditolak ------------

test('admin, owner, gudang bisa melihat katalog produk', function (string $role) {
    $this->actingAs(userWithRole($role))->get('/products')->assertOk();
})->with(['admin', 'owner', 'gudang']);

test('kasir tidak bisa melihat katalog produk', function () {
    $this->actingAs(userWithRole('kasir'))->get('/products')->assertForbidden();
});

// --- Manajemen akun & data master: admin & owner ------------------------------

test('admin & owner bisa membuka manajemen akun', function (string $role) {
    $this->actingAs(userWithRole($role))->get('/users')->assertOk();
})->with(['admin', 'owner']);

test('gudang & kasir ditolak manajemen akun', function (string $role) {
    $this->actingAs(userWithRole($role))->get('/users')->assertForbidden();
})->with(['gudang', 'kasir']);

// --- Penjualan: admin, owner, kasir; gudang ditolak ---------------------------

test('admin, owner, kasir bisa membuka form penjualan', function (string $role) {
    $this->actingAs(userWithRole($role))->get('/sales/create')->assertOk();
})->with(['admin', 'owner', 'kasir']);

test('gudang ditolak form penjualan', function () {
    $this->actingAs(userWithRole('gudang'))->get('/sales/create')->assertForbidden();
});

// --- Pembelian: admin, owner, gudang; kasir ditolak ---------------------------

test('admin, owner, gudang bisa membuka form pembelian', function (string $role) {
    $this->actingAs(userWithRole($role))->get('/purchases/create')->assertOk();
})->with(['admin', 'owner', 'gudang']);

test('kasir ditolak form pembelian', function () {
    $this->actingAs(userWithRole('kasir'))->get('/purchases/create')->assertForbidden();
});

// --- Harga varian tersimpan ---------------------------------------------------

test('admin bisa menambah varian beserta harga', function () {
    $product = makeProduct();

    $this->actingAs(userWithRole('admin'))
        ->post("/products/{$product->id}/variants", [
            'model' => 'Slim Fit',
            'color' => 'Navy',
            'price' => 175000,
            'size'  => 'L',
        ])
        ->assertRedirect();

    $variant = ProductVariant::where('product_id', $product->id)->first();

    expect($variant)->not->toBeNull();
    expect((float) $variant->price)->toBe(175000.0);
    expect($variant->stock)->toBe(0);
});

test('gudang ditolak menambah varian', function () {
    $product = makeProduct();

    $this->actingAs(userWithRole('gudang'))
        ->post("/products/{$product->id}/variants", ['model' => 'X', 'price' => 1000])
        ->assertForbidden();
});

// --- Role owner valid dibuat lewat manajemen akun -----------------------------

test('admin bisa membuat akun ber-role owner', function () {
    $this->actingAs(userWithRole('admin'))
        ->post('/users', [
            'name'                  => 'Pemilik Toko',
            'email'                 => 'pemilik@kasyraa.co',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'owner',
        ])
        ->assertRedirect('/users');

    expect(User::where('email', 'pemilik@kasyraa.co')->value('role'))->toBe('owner');
});

test('role tidak valid ditolak saat membuat akun', function () {
    $this->actingAs(userWithRole('admin'))
        ->post('/users', [
            'name'                  => 'Salah Role',
            'email'                 => 'salah@kasyraa.co',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'superuser',
        ])
        ->assertSessionHasErrors('role');
});
