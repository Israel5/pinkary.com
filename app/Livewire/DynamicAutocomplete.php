<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\DynamicAutocomplete\DynamicAutocompleteService as AutocompleteService;
use App\Services\DynamicAutocomplete\Results\Collection;
use App\Services\DynamicAutocomplete\Types\Type;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * @property-read array<string, array<string, string>> $autocompleteTypes
 * @property-read Collection $autocompleteResults
 */
final class DynamicAutocomplete extends Component
{
    /**
     * An array of matched type aliases (like ["mentions", ...]).
     *
     * @var array<int, string>
     */
    public array $matchedTypes = [];

    /**
     * The matched term to search.
     */
    public string $query = '';

    /**
     * The autocomplete service.
     */
    private AutocompleteService $autocompleteService;

    /**
     * Boot the component.
     */
    public function boot(AutocompleteService $service): void
    {
        $this->autocompleteService = $service;
    }

    /**
     * Get the available autocomplete types.
     *
     * @return array<string, array<string, string>>
     */
    #[Computed]
    public function autocompleteTypes(): array
    {
        return collect($this->autocompleteService::types())
            /** @param class-string<Type> $type */
            ->map(fn (string $type): array => $type::make()->toArray())
            ->all();
    }

    /**
     * Set the required search properties on the component.
     *
     * @param  array<int, string>  $matchedTypes
     */
    public function setAutocompleteSearchParams(array $matchedTypes, string $query): void
    {
        $this->matchedTypes = array_intersect($matchedTypes, array_keys($this->autocompleteTypes));
        $this->query = $this->matchedTypes === [] ? '' : $query;
    }

    /**
     * Get the autocomplete results (aka options) for the matched types
     * and search query previously set on the component.
     */
    #[Computed]
    public function autocompleteResults(): Collection
    {
        $results = collect($this->matchedTypes)
            ->map(fn (string $typeAlias): Collection => $this->autocompleteService
                ->search($typeAlias, $this->query)
            )
            ->flatten(1);

        return Collection::make($results);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.dynamic-autocomplete');
    }
}
