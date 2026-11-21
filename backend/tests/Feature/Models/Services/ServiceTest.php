<?php

use App\Enums\PricingModel;
use App\Enums\ServiceFeatureKind;
use App\Models\Catalog\Technology;
use App\Models\Services\Service;
use App\Models\Services\ServiceFeature;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('stores a priced service and displays its price', function () {
    $service = Service::factory()->priced('1500.00', 'EUR')->create();

    expect($service->refresh())
        ->pricing_model->toBe(PricingModel::Fixed)
        ->price_from->toBe('1500.00')
        ->price_currency->toBe('EUR')
        ->hasDisplayablePrice()->toBeTrue();
});

it('does not display any price when none is filled in', function () {
    $service = Service::factory()->create();

    expect($service->hasDisplayablePrice())->toBeFalse();
});

it('rejects inconsistent prices and delivery times at the database level', function (array $values, string $constraint) {
    $service = Service::factory()->create();

    expect(fn () => DB::table('services')->where('id', $service->id)->update($values))
        ->toThrow(QueryException::class, $constraint);
})->with([
    'prix négatif' => [['price_from' => -10, 'price_currency' => 'EUR'], 'services_price_check'],
    'prix sans devise' => [['price_from' => 100], 'services_price_requires_currency_check'],
    'devise invalide' => [['price_from' => 100, 'price_currency' => 'eur'], 'services_price_currency_check'],
    'devis avec prix' => [['pricing_model' => 'quote', 'price_from' => 100, 'price_currency' => 'EUR'], 'services_quote_without_price_check'],
    'mode inconnu' => [['pricing_model' => 'yearly'], 'services_pricing_model_check'],
    'délai max négatif seul' => [['delivery_max_days' => -1], 'services_delivery_max_check'],
    'délai min négatif' => [['delivery_min_days' => -2], 'services_delivery_min_check'],
    'délai max inférieur au min' => [['delivery_min_days' => 10, 'delivery_max_days' => 5], 'services_delivery_range_check'],
]);

it('accepts a single delivery bound', function (array $values) {
    $service = Service::factory()->create($values);

    expect($service->refresh()->exists)->toBeTrue();
})->with([
    'minimum seul' => [['delivery_min_days' => 5]],
    'maximum seul' => [['delivery_max_days' => 30]],
]);

it('shows only published services', function () {
    $published = Service::factory()->published()->create();
    Service::factory()->create();

    expect(Service::published()->pluck('id')->all())->toBe([$published->id]);
});

it('lists the visible features of a service by kind and order', function () {
    $service = Service::factory()->create();
    ServiceFeature::factory()->for($service)->create(['kind' => ServiceFeatureKind::Benefit, 'position' => 2]);
    $first = ServiceFeature::factory()->for($service)->create(['kind' => ServiceFeatureKind::Deliverable, 'position' => 1]);

    expect($service->features()->ordered()->first()->is($first))->toBeTrue()
        ->and($service->features()->where('kind', ServiceFeatureKind::Benefit)->count())->toBe(1);
});

it('rejects an unknown feature kind at the database level', function () {
    $feature = ServiceFeature::factory()->create();

    DB::table('service_features')->where('id', $feature->id)->update(['kind' => 'bonus']);
})->throws(QueryException::class, 'service_features_kind_check');

it('reuses technologies from the central catalogue', function () {
    $service = Service::factory()->create();
    $technology = Technology::factory()->create();

    $service->technologies()->attach($technology);

    expect($service->technologies()->pluck('technologies.id')->all())->toBe([$technology->id]);
});

it('finds a service with the full text search', function () {
    $service = Service::factory()->create(['fr' => [
        'title' => 'Création de sites e-commerce', 'slug' => 'sites-e-commerce',
        'summary' => 'Boutiques en ligne', 'description' => 'Paiement sécurisé et gestion des stocks.',
    ]]);
    Service::factory()->create();

    expect(Service::search('securise', 'fr')->pluck('id')->all())->toBe([$service->id]);
});

it('refuses to delete a category that still contains services', function () {
    $service = Service::factory()->create();

    $service->category->delete();
})->throws(QueryException::class, 'services_service_category_id_foreign');
