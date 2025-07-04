<?php

namespace Filament\Schemas\Concerns;

use Closure;
use Exception;
use Filament\Infolists\Components\Entry;
use Filament\Support\Livewire\Partials\PartialsComponentHook;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

trait HasState
{
    protected ?string $statePath = null;

    protected string $cachedAbsoluteStatePath;

    /**
     * @var array<string, mixed> | null
     */
    protected ?array $constantState = null;

    protected bool | Closure $shouldPartiallyRender = false;

    /**
     * @param  array<string, mixed> | null  $state
     */
    public function state(?array $state): static
    {
        $this->constantState($state);

        return $this;
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public function rawState(array $state): static
    {
        $livewire = $this->getLivewire();

        if ($statePath = $this->getStatePath()) {
            data_set($livewire, $statePath, $state);
        } else {
            foreach ($state as $key => $value) {
                data_set($livewire, $key, $value);
            }
        }

        return $this;
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public function partialRawState(array $state): static
    {
        $livewire = $this->getLivewire();

        if ($statePath = $this->getStatePath()) {
            foreach ($state as $key => $value) {
                data_set($livewire, "{$statePath}.{$key}", $value);
            }
        } else {
            foreach ($state as $key => $value) {
                data_set($livewire, $key, $value);
            }
        }

        return $this;
    }

    /**
     * @param  array<string, mixed> | null  $state
     */
    public function constantState(?array $state): static
    {
        $this->constantState = $state;

        return $this;
    }

    public function partiallyRender(bool | Closure $condition = true): static
    {
        $this->shouldPartiallyRender = $condition;

        return $this;
    }

    public function callAfterStateHydrated(): void
    {
        foreach ($this->getComponents(withActions: false, withHidden: true) as $component) {
            $component->callAfterStateHydrated();

            foreach ($component->getChildSchemas(withHidden: true) as $childSchema) {
                $childSchema->callAfterStateHydrated();
            }
        }
    }

    public function callAfterStateUpdated(string $path): bool
    {
        try {
            foreach ($this->getComponents(withActions: false, withHidden: true) as $component) {
                $componentStatePath = $component->getStatePath();

                if ($componentStatePath === $path) {
                    $component->callAfterStateUpdated(shouldBubbleToParents: false);

                    return true;
                }

                if (str($path)->startsWith("{$componentStatePath}.")) {
                    $component->callAfterStateUpdated(shouldBubbleToParents: false);
                }

                foreach ($component->getChildSchemas() as $childSchema) {
                    if ($childSchema->callAfterStateUpdated($path)) {
                        return true;
                    }
                }
            }

            return false;
        } finally {
            if ($this->shouldPartiallyRender($path)) {
                app(PartialsComponentHook::class)->renderPartial($this->getLivewire(), fn (): array => [
                    "schema.{$this->getKey()}" => $this->render(),
                ]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public function callBeforeStateDehydrated(array &$state = []): void
    {
        foreach ($this->getComponents(withActions: false, withHidden: true) as $component) {
            if ($component->isHidden()) {
                continue;
            }

            $component->callBeforeStateDehydrated($state);

            foreach ($component->getChildSchemas() as $childSchema) {
                if ($childSchema->isHidden()) {
                    continue;
                }

                $childSchema->callBeforeStateDehydrated($state);
            }
        }
    }

    public function hasDehydratedComponent(string $statePath): bool
    {
        foreach ($this->getComponents(withActions: false, withHidden: true) as $component) {
            if (! $component->isDehydrated()) {
                continue;
            }

            if ($component->hasStatePath() && ($component->getStatePath() === $statePath)) {
                return true;
            }

            foreach ($component->getChildSchemas(withHidden: true) as $container) {
                if ($container->hasDehydratedComponent($statePath)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function dehydrateState(array &$state = [], bool $isDehydrated = true): array
    {
        foreach ($this->getComponents(withActions: false, withHidden: true) as $component) {
            $component->dehydrateState($state, $isDehydrated);
        }

        return $state;
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function mutateDehydratedState(array &$state = []): array
    {
        foreach ($this->getComponents(withActions: false, withHidden: true) as $component) {
            if (! $component->isDehydrated()) {
                continue;
            }

            foreach ($component->getChildSchemas() as $childSchema) {
                if ($childSchema->isHidden()) {
                    continue;
                }

                $childSchema->mutateDehydratedState($state);
            }

            if (filled($component->getStatePath(isAbsolute: false))) {
                if (! $component->mutatesDehydratedState()) {
                    continue;
                }

                $componentStatePath = $component->getStatePath();

                data_set(
                    $state,
                    $componentStatePath,
                    $component->mutateDehydratedState(
                        data_get($state, $componentStatePath),
                    ),
                );
            }
        }

        return $state;
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function mutateStateForValidation(array &$state = []): array
    {
        foreach ($this->getComponents(withActions: false, withHidden: true) as $component) {
            if ($component->isHiddenAndNotDehydratedWhenHidden()) {
                continue;
            }

            foreach ($component->getChildSchemas() as $childSchema) {
                if ($childSchema->isHidden()) {
                    continue;
                }

                $childSchema->mutateStateForValidation($state);
            }

            if (filled($component->getStatePath(isAbsolute: false))) {
                if (! $component->mutatesStateForValidation()) {
                    continue;
                }

                $componentStatePath = $component->getStatePath();

                data_set(
                    $state,
                    $componentStatePath,
                    $component->mutateStateForValidation(
                        data_get($state, $componentStatePath),
                    ),
                );
            }
        }

        return $state;
    }

    /**
     * @param  array<string, mixed> | null  $state
     */
    public function fill(?array $state = null, bool $shouldCallHydrationHooks = true, bool $shouldFillStateWithNull = true): static
    {
        $hydratedDefaultState = null;

        if ($state === null) {
            $hydratedDefaultState = [];
        } else {
            $this->rawState($state);
        }

        $this->hydrateState($hydratedDefaultState, $shouldCallHydrationHooks);

        if ($shouldFillStateWithNull) {
            $this->fillStateWithNull();
        }

        return $this;
    }

    /**
     * @param  array<string, mixed>  $state
     * @param  array<string>  $statePaths
     */
    public function fillPartially(array $state, array $statePaths, bool $shouldCallHydrationHooks = true, bool $shouldFillStateWithNull = true): static
    {
        $this->partialRawState(collect($state)->dot()->only($statePaths)->all());

        if ($schemaStatePath = $this->getStatePath()) {
            $statePaths = array_map(
                fn (string $statePath): string => "{$schemaStatePath}.{$statePath}",
                $statePaths,
            );
        }

        $this->hydrateStatePartially(
            $statePaths,
            $shouldCallHydrationHooks,
        );

        if ($shouldFillStateWithNull) {
            $this->fillStateWithNull();
        }

        return $this;
    }

    /**
     * @param  array<string, mixed> | null  $hydratedDefaultState
     */
    public function hydrateState(?array &$hydratedDefaultState, bool $shouldCallHydrationHooks = true): void
    {
        foreach ($this->getComponents(withActions: false, withHidden: true) as $component) {
            if ($component instanceof Entry) {
                continue;
            }

            $component->hydrateState($hydratedDefaultState, $shouldCallHydrationHooks);
        }
    }

    /**
     * @param  array<string>  $statePaths
     */
    public function hydrateStatePartially(array $statePaths, bool $shouldCallHydrationHooks = true): void
    {
        foreach ($this->getComponents(withActions: false, withHidden: true) as $component) {
            if ($component instanceof Entry) {
                continue;
            }

            $component->hydrateStatePartially($statePaths, $shouldCallHydrationHooks);
        }
    }

    public function fillStateWithNull(): void
    {
        foreach ($this->getComponents(withActions: false, withHidden: true) as $component) {
            $component->fillStateWithNull();
        }
    }

    public function statePath(?string $path): static
    {
        $this->statePath = $path;

        return $this;
    }

    /**
     * @internal Do not use this method outside the internals of Filament. It is subject to breaking changes in minor and patch releases.
     *
     * @return Model | array<string, mixed>
     */
    public function getConstantState(): Model | array
    {
        return $this->constantState ?? $this->getRecord(withParentComponentRecord: false) ?? $this->getParentComponent()?->getContainer()->getConstantState() ?? $this->getRecord() ?? throw new Exception('Schema has no [record()] or [state()] set.');
    }

    /**
     * @internal Do not use this method outside the internals of Filament. It is subject to breaking changes in minor and patch releases.
     */
    public function getConstantStatePath(): ?string
    {
        if ($this->constantState !== null) {
            return $this->getStatePath();
        }

        if ($this->getRecord(withParentComponentRecord: false) !== null) {
            return $this->getStatePath();
        }

        if ($this->getParentComponent()?->getContainer()->getConstantState() !== null) {
            return $this->getParentComponent()->getContainer()->getStatePath();
        }

        return $this->getParentComponent()?->getRecordConstantStatePath();
    }

    /**
     * @return array<string, mixed>
     */
    public function getState(bool $shouldCallHooksBefore = true, ?Closure $afterValidate = null): array
    {
        $state = $this->validate();

        if ($shouldCallHooksBefore) {
            $this->callBeforeStateDehydrated($state);

            $afterValidate || $this->saveRelationships();
            $afterValidate || $this->loadStateFromRelationships(shouldHydrate: true);
        }

        $this->dehydrateState($state);
        $this->mutateDehydratedState($state);

        if ($statePath = $this->getStatePath()) {
            $state = data_get($state, $statePath) ?? [];
        }

        if ($afterValidate) {
            value($afterValidate, $state);

            $shouldCallHooksBefore && $this->saveRelationships();
            $shouldCallHooksBefore && $this->loadStateFromRelationships(shouldHydrate: true);
        }

        return $state;
    }

    /**
     * @return array<string, mixed> | Arrayable
     */
    public function getRawState(): array | Arrayable
    {
        return data_get($this->getLivewire(), $this->getStatePath()) ?? [];
    }

    /**
     * @param  array<string>  $keys
     * @return array<string, mixed>
     */
    public function getStateOnly(array $keys, bool $shouldCallHooksBefore = true): array
    {
        return Arr::only($this->getState($shouldCallHooksBefore), $keys);
    }

    /**
     * @param  array<string>  $keys
     * @return array<string, mixed>
     */
    public function getStateExcept(array $keys, bool $shouldCallHooksBefore = true): array
    {
        return Arr::except($this->getState($shouldCallHooksBefore), $keys);
    }

    public function getStatePath(bool $isAbsolute = true): ?string
    {
        if (! $isAbsolute) {
            return $this->statePath;
        }

        if (isset($this->cachedAbsoluteStatePath)) {
            return $this->cachedAbsoluteStatePath;
        }

        $pathComponents = [];

        if ($parentComponentStatePath = $this->getParentComponent()?->getStatePath()) {
            $pathComponents[] = $parentComponentStatePath;
        }

        if (filled($statePath = $this->statePath)) {
            $pathComponents[] = $statePath;
        }

        return $this->cachedAbsoluteStatePath = implode('.', $pathComponents);
    }

    protected function flushCachedAbsoluteStatePath(): void
    {
        unset($this->cachedAbsoluteStatePath);
    }

    public function shouldPartiallyRender(?string $updatedStatePath = null): bool
    {
        if (! $this->evaluate($this->shouldPartiallyRender)) {
            return false;
        }

        if (blank($this->getKey())) {
            throw new Exception('You cannot partially render a schema without a [key()] or [statePath()] defined.');
        }

        return blank($updatedStatePath) || str($updatedStatePath)->startsWith("{$this->getStatePath()}.");
    }
}
