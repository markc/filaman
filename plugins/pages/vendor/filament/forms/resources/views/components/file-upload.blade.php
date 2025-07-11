@php
    use Filament\Support\Enums\Alignment;
    use Filament\Support\Facades\FilamentView;

    $fieldWrapperView = $getFieldWrapperView();
    $imageCropAspectRatio = $getImageCropAspectRatio();
    $imageResizeTargetHeight = $getImageResizeTargetHeight();
    $imageResizeTargetWidth = $getImageResizeTargetWidth();
    $isAvatar = $isAvatar();
    $isMultiple = $isMultiple();
    $key = $getKey();
    $statePath = $getStatePath();
    $isDisabled = $isDisabled();
    $hasImageEditor = $hasImageEditor();
    $hasCircleCropper = $hasCircleCropper();

    $alignment = $getAlignment() ?? Alignment::Start;

    if (! $alignment instanceof Alignment) {
        $alignment = filled($alignment) ? (Alignment::tryFrom($alignment) ?? $alignment) : null;
    }
@endphp

<x-dynamic-component
    :component="$fieldWrapperView"
    :field="$field"
    :label-sr-only="$isLabelHidden()"
>
    <div
        @if (FilamentView::hasSpaMode())
            {{-- format-ignore-start --}}x-load="visible || event (x-modal-opened)"{{-- format-ignore-end --}}
        @else
            x-load
        @endif
        x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('file-upload', 'filament/forms') }}"
        x-data="fileUploadFormComponent({
                    acceptedFileTypes: @js($getAcceptedFileTypes()),
                    imageEditorEmptyFillColor: @js($getImageEditorEmptyFillColor()),
                    imageEditorMode: @js($getImageEditorMode()),
                    imageEditorViewportHeight: @js($getImageEditorViewportHeight()),
                    imageEditorViewportWidth: @js($getImageEditorViewportWidth()),
                    deleteUploadedFileUsing: async (fileKey) => {
                        return await $wire.callSchemaComponentMethod(
                            @js($key),
                            'deleteUploadedFile',
                            { fileKey },
                        )
                    },
                    getUploadedFilesUsing: async () => {
                        return await $wire.callSchemaComponentMethod(
                            @js($key),
                            'getUploadedFiles',
                        )
                    },
                    hasImageEditor: @js($hasImageEditor),
                    hasCircleCropper: @js($hasCircleCropper),
                    canEditSvgs: @js($canEditSvgs()),
                    isSvgEditingConfirmed: @js($isSvgEditingConfirmed()),
                    confirmSvgEditingMessage: @js(__('filament-forms::components.file_upload.editor.svg.messages.confirmation')),
                    disabledSvgEditingMessage: @js(__('filament-forms::components.file_upload.editor.svg.messages.disabled')),
                    imageCropAspectRatio: @js($imageCropAspectRatio),
                    imagePreviewHeight: @js($getImagePreviewHeight()),
                    imageResizeMode: @js($getImageResizeMode()),
                    imageResizeTargetHeight: @js($imageResizeTargetHeight),
                    imageResizeTargetWidth: @js($imageResizeTargetWidth),
                    imageResizeUpscale: @js($getImageResizeUpscale()),
                    isAvatar: @js($isAvatar),
                    isDeletable: @js($isDeletable()),
                    isDisabled: @js($isDisabled),
                    isDownloadable: @js($isDownloadable()),
                    isMultiple: @js($isMultiple),
                    isOpenable: @js($isOpenable()),
                    isPasteable: @js($isPasteable()),
                    isPreviewable: @js($isPreviewable()),
                    isReorderable: @js($isReorderable()),
                    itemPanelAspectRatio: @js($getItemPanelAspectRatio()),
                    loadingIndicatorPosition: @js($getLoadingIndicatorPosition()),
                    locale: @js(app()->getLocale()),
                    panelAspectRatio: @js($getPanelAspectRatio()),
                    panelLayout: @js($getPanelLayout()),
                    placeholder: @js($getPlaceholder()),
                    maxFiles: @js($getMaxFiles()),
                    maxSize: @js(($size = $getMaxSize()) ? "{$size}KB" : null),
                    minSize: @js(($size = $getMinSize()) ? "{$size}KB" : null),
                    mimeTypeMap: @js($getMimeTypeMap()),
                    maxParallelUploads: @js($getMaxParallelUploads()),
                    removeUploadedFileUsing: async (fileKey) => {
                        return await $wire.callSchemaComponentMethod(
                            @js($key),
                            'removeUploadedFile',
                            { fileKey },
                        )
                    },
                    removeUploadedFileButtonPosition: @js($getRemoveUploadedFileButtonPosition()),
                    reorderUploadedFilesUsing: async (fileKeys) => {
                        return await $wire.callSchemaComponentMethod(
                            @js($key),
                            'reorderUploadedFiles',
                            { fileKeys },
                        )
                    },
                    shouldAppendFiles: @js($shouldAppendFiles()),
                    shouldOrientImageFromExif: @js($shouldOrientImagesFromExif()),
                    shouldTransformImage: @js($imageCropAspectRatio || $imageResizeTargetHeight || $imageResizeTargetWidth),
                    state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
                    uploadButtonPosition: @js($getUploadButtonPosition()),
                    uploadingMessage: @js($getUploadingMessage()),
                    uploadProgressIndicatorPosition: @js($getUploadProgressIndicatorPosition()),
                    uploadUsing: (fileKey, file, success, error, progress) => {
                        $wire.upload(
                            `{{ $statePath }}.${fileKey}`,
                            file,
                            () => {
                                success(fileKey)
                            },
                            error,
                            (progressEvent) => {
                                progress(true, progressEvent.detail.progress, 100)
                            },
                        )
                    },
                })"
        wire:ignore
        {{
            $attributes
                ->merge([
                    'id' => $getId(),
                ], escape: false)
                ->merge($getExtraAttributes(), escape: false)
                ->merge($getExtraAlpineAttributes(), escape: false)
                ->class([
                    'fi-fo-file-upload',
                    'fi-fo-file-upload-avatar' => $isAvatar,
                    ($alignment instanceof Alignment) ? "fi-align-{$alignment->value}" : $alignment,
                ])
        }}
    >
        <div class="fi-fo-file-upload-input-ctn">
            <input
                x-ref="input"
                {{
                    $getExtraInputAttributeBag()
                        ->merge([
                            'disabled' => $isDisabled,
                            'multiple' => $isMultiple,
                            'type' => 'file',
                        ], escape: false)
                }}
            />
        </div>

        <div
            x-show="error"
            x-text="error"
            x-cloak
            class="fi-fo-file-upload-error-message"
        ></div>

        @if ($hasImageEditor && (! $isDisabled))
            <div
                x-show="isEditorOpen"
                x-cloak
                x-on:click.stop=""
                x-trap.noscroll="isEditorOpen"
                x-on:keydown.escape.window="closeEditor"
                @class([
                    'fi-fo-file-upload-editor',
                    'fi-fo-file-upload-editor-circle-cropper' => $hasCircleCropper,
                ])
            >
                <div
                    aria-hidden="true"
                    class="fi-fo-file-upload-editor-overlay"
                ></div>

                <div class="fi-fo-file-upload-editor-window">
                    <div class="fi-fo-file-upload-editor-image-ctn">
                        <img
                            x-ref="editor"
                            class="fi-fo-file-upload-editor-image"
                        />
                    </div>

                    <div class="fi-fo-file-upload-editor-control-panel">
                        <div
                            class="fi-fo-file-upload-editor-control-panel-main"
                        >
                            <div
                                class="fi-fo-file-upload-editor-control-panel-group"
                            >
                                @foreach ([
                                    [
                                        'label' => __('filament-forms::components.file_upload.editor.fields.x_position.label'),
                                        'ref' => 'xPositionInput',
                                        'unit' => __('filament-forms::components.file_upload.editor.fields.x_position.unit'),
                                        'alpineSaveHandler' => 'editor.setData({...editor.getData(true), x: +$el.value})',
                                    ],
                                    [
                                        'label' => __('filament-forms::components.file_upload.editor.fields.y_position.label'),
                                        'ref' => 'yPositionInput',
                                        'unit' => __('filament-forms::components.file_upload.editor.fields.y_position.unit'),
                                        'alpineSaveHandler' => 'editor.setData({...editor.getData(true), y: +$el.value})',
                                    ],
                                    [
                                        'label' => __('filament-forms::components.file_upload.editor.fields.width.label'),
                                        'ref' => 'widthInput',
                                        'unit' => __('filament-forms::components.file_upload.editor.fields.width.unit'),
                                        'alpineSaveHandler' => 'editor.setData({...editor.getData(true), width: +$el.value})',
                                    ],
                                    [
                                        'label' => __('filament-forms::components.file_upload.editor.fields.height.label'),
                                        'ref' => 'heightInput',
                                        'unit' => __('filament-forms::components.file_upload.editor.fields.height.unit'),
                                        'alpineSaveHandler' => 'editor.setData({...editor.getData(true), height: +$el.value})',
                                    ],
                                    [
                                        'label' => __('filament-forms::components.file_upload.editor.fields.rotation.label'),
                                        'ref' => 'rotationInput',
                                        'unit' => __('filament-forms::components.file_upload.editor.fields.rotation.unit'),
                                        'alpineSaveHandler' => 'editor.rotateTo(+$el.value)',
                                    ],
                                ] as $input)
                                    <label>
                                        <x-filament::input.wrapper>
                                            <x-slot name="prefix">
                                                {{ $input['label'] }}
                                            </x-slot>

                                            <input
                                                x-on:keyup.enter.stop.prevent="{!! $input['alpineSaveHandler'] !!}"
                                                x-on:blur="{!! $input['alpineSaveHandler'] !!}"
                                                x-ref="{{ $input['ref'] }}"
                                                x-on:keydown.enter.prevent
                                                type="text"
                                                class="fi-input"
                                            />

                                            <x-slot name="suffix">
                                                {{ $input['unit'] }}
                                            </x-slot>
                                        </x-filament::input.wrapper>
                                    </label>
                                @endforeach
                            </div>

                            <div
                                class="fi-fo-file-upload-editor-control-panel-group"
                            >
                                @foreach ($getImageEditorActions() as $groupedActions)
                                    <div class="fi-btn-group">
                                        @foreach ($groupedActions as $action)
                                            <button
                                                aria-label="{{ $action['label'] }}"
                                                type="button"
                                                x-on:click.stop.prevent="{{ $action['alpineClickHandler'] }}"
                                                x-tooltip="{ content: @js($action['label']), theme: $store.theme }"
                                                class="fi-btn"
                                            >
                                                {{ $action['iconHtml'] }}
                                            </button>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>

                            @if (count($aspectRatios = $getImageEditorAspectRatiosForJs()))
                                <div
                                    class="fi-fo-file-upload-editor-control-panel-group"
                                >
                                    <div
                                        class="fi-fo-file-upload-editor-control-panel-group-title"
                                    >
                                        {{ __('filament-forms::components.file_upload.editor.aspect_ratios.label') }}
                                    </div>

                                    @foreach (collect($aspectRatios)->chunk(5) as $ratiosChunk)
                                        <div class="fi-btn-group">
                                            @foreach ($ratiosChunk as $label => $ratio)
                                                <button
                                                    type="button"
                                                    x-on:click.stop.prevent="
                                                        currentRatio = @js($label) {!! ';' !!}
                                                        editor.setAspectRatio(@js($ratio))
                                                    "
                                                    x-tooltip="{ content: @js(__('filament-forms::components.file_upload.editor.actions.set_aspect_ratio.label', ['ratio' => $label])), theme: $store.theme }"
                                                    x-bind:class="{ 'fi-active': currentRatio === @js($label) }"
                                                    class="fi-btn"
                                                >
                                                    {{ $label }}
                                                </button>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div
                            class="fi-fo-file-upload-editor-control-panel-footer"
                        >
                            <button
                                type="button"
                                x-on:click.prevent="pond.imageEditEditor.oncancel"
                                class="fi-btn"
                            >
                                {{ __('filament-forms::components.file_upload.editor.actions.cancel.label') }}
                            </button>

                            <button
                                type="button"
                                x-on:click.stop.prevent="editor.reset()"
                                {{
                                    (new \Illuminate\View\ComponentAttributeBag)
                                        ->color(\Filament\Support\View\Components\ButtonComponent::class, 'danger')
                                        ->class(['fi-btn fi-fo-file-upload-editor-control-panel-reset-action'])
                                }}
                            >
                                {{ __('filament-forms::components.file_upload.editor.actions.reset.label') }}
                            </button>

                            <button
                                type="button"
                                x-on:click.prevent="saveEditor"
                                {{
                                    (new \Illuminate\View\ComponentAttributeBag)
                                        ->color(\Filament\Support\View\Components\ButtonComponent::class, 'success')
                                        ->class(['fi-btn'])
                                }}
                            >
                                {{ __('filament-forms::components.file_upload.editor.actions.save.label') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-dynamic-component>
