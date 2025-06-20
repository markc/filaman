<?php

namespace Filament\Schemas\Concerns;

use Illuminate\Database\Eloquent\Model;

trait BelongsToModel
{
    /**
     * @var Model | array<string, mixed> | class-string<Model> | null
     */
    public Model | array | string | null $model = null;

    /**
     * @param  Model | array<string, mixed> | class-string<Model> | null  $model
     */
    public function model(Model | array | string | null $model = null): static
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @param  Model | array<string, mixed> | null  $record
     */
    public function record(Model | array | null $record): static
    {
        $this->model($record);

        return $this;
    }

    public function saveRelationships(): void
    {
        foreach ($this->getComponents(withActions: false, withHidden: true) as $component) {
            $component->saveRelationshipsBeforeChildren();

            $shouldSaveRelationshipsWhenDisabled = $component->shouldSaveRelationshipsWhenDisabled();

            foreach ($component->getChildSchemas(withHidden: $component->shouldSaveRelationshipsWhenHidden()) as $childSchema) {
                if ((! $shouldSaveRelationshipsWhenDisabled) && $childSchema->isDisabled()) {
                    continue;
                }

                $childSchema->saveRelationships();
            }

            $component->saveRelationships();
        }
    }

    public function loadStateFromRelationships(bool $shouldHydrate = false): void
    {
        foreach ($this->getComponents(withActions: false, withHidden: true) as $component) {
            $component->loadStateFromRelationships($shouldHydrate);

            foreach ($component->getChildSchemas(withHidden: true) as $childSchema) {
                $childSchema->loadStateFromRelationships($shouldHydrate);
            }
        }
    }

    /**
     * @return class-string<Model>|null
     */
    public function getModel(): ?string
    {
        $model = $this->model;

        if ($model instanceof Model) {
            return $model::class;
        }

        if (is_array($model)) {
            return null;
        }

        if (filled($model)) {
            return $model;
        }

        return $this->getParentComponent()?->getModel();
    }

    /**
     * @return Model | array<string, mixed> | null
     */
    public function getRecord(bool $withParentComponentRecord = true): Model | array | null
    {
        $model = $this->model;

        if (($model instanceof Model) || is_array($model)) {
            return $model;
        }

        if (is_string($model)) {
            return null;
        }

        if (! $withParentComponentRecord) {
            return null;
        }

        return $this->getParentComponent()?->getRecord();
    }

    public function getModelInstance(): ?Model
    {
        $model = $this->model;

        if (($model === null) || is_array($model)) {
            return $this->getParentComponent()?->getModelInstance();
        }

        if ($model instanceof Model) {
            return $model;
        }

        return app($model);
    }
}
