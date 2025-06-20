<?php

return [

    'builder' => [

        'actions' => [

            'clone' => [
                'label' => 'Clonar',
            ],

            'add' => [

                'label' => 'Adicionar em :label',

                'modal' => [

                    'heading' => 'Adicionar em :label',

                    'actions' => [

                        'add' => [
                            'label' => 'Adicionar',
                        ],

                    ],

                ],

            ],

            'add_between' => [

                'label' => 'Inserir entre blocos',

                'modal' => [

                    'heading' => 'Adicionar em :label',

                    'actions' => [

                        'add' => [
                            'label' => 'Adicionar',
                        ],

                    ],

                ],

            ],

            'delete' => [
                'label' => 'Excluir',
            ],

            'edit' => [

                'label' => 'Editar',

                'modal' => [

                    'heading' => 'Editar bloco',

                    'actions' => [

                        'save' => [
                            'label' => 'Salvar alterações',
                        ],

                    ],

                ],

            ],

            'reorder' => [
                'label' => 'Mover',
            ],

            'move_down' => [
                'label' => 'Mover para baixo',
            ],

            'move_up' => [
                'label' => 'Mover para cima',
            ],

            'collapse' => [
                'label' => 'Recolher',
            ],

            'expand' => [
                'label' => 'Expandir',
            ],

            'collapse_all' => [
                'label' => 'Recolher todos',
            ],

            'expand_all' => [
                'label' => 'Expandir todos',
            ],

        ],

    ],

    'checkbox_list' => [

        'actions' => [

            'deselect_all' => [
                'label' => 'Desmarcar todos',
            ],

            'select_all' => [
                'label' => 'Marcar todos',
            ],

        ],

    ],

    'file_upload' => [

        'editor' => [

            'actions' => [

                'cancel' => [
                    'label' => 'Cancelar',
                ],

                'drag_crop' => [
                    'label' => 'Modo de arrastar "cortar"',
                ],

                'drag_move' => [
                    'label' => 'Modo de arrastar "mover"',
                ],

                'flip_horizontal' => [
                    'label' => 'Inverter imagem horizontalmente',
                ],

                'flip_vertical' => [
                    'label' => 'Inverter imagem verticalmente',
                ],

                'move_down' => [
                    'label' => 'Mover imagem para baixo',
                ],

                'move_left' => [
                    'label' => 'Mover imagem para esquerda',
                ],

                'move_right' => [
                    'label' => 'Mover imagem para direita',
                ],

                'move_up' => [
                    'label' => 'Mover imagem para cima',
                ],

                'reset' => [
                    'label' => 'Limpar',
                ],

                'rotate_left' => [
                    'label' => 'Rotacionar imagem para esquerda',
                ],

                'rotate_right' => [
                    'label' => 'Rotacionar imagem para direita',
                ],

                'set_aspect_ratio' => [
                    'label' => 'Definir proporção para :ratio',
                ],

                'save' => [
                    'label' => 'Salvar',
                ],

                'zoom_100' => [
                    'label' => 'Ampliar imagem para 100%',
                ],

                'zoom_in' => [
                    'label' => 'Mais zoom',
                ],

                'zoom_out' => [
                    'label' => 'Menos zoom',
                ],

            ],

            'fields' => [

                'height' => [
                    'label' => 'Altura',
                    'unit' => 'px',
                ],

                'rotation' => [
                    'label' => 'Rotação',
                    'unit' => 'deg',
                ],

                'width' => [
                    'label' => 'Largura',
                    'unit' => 'px',
                ],

                'x_position' => [
                    'label' => 'X',
                    'unit' => 'px',
                ],

                'y_position' => [
                    'label' => 'Y',
                    'unit' => 'px',
                ],

            ],

            'aspect_ratios' => [

                'label' => 'Proporções',

                'no_fixed' => [
                    'label' => 'Livre',
                ],

            ],

            'svg' => [

                'messages' => [
                    'confirmation' => 'Não é recomendado editar arquivos SVG, pois pode resultar em perda de qualidade ao dimensionar.\n Você tem certeza de que deseja continuar?',
                    'disabled' => 'A edição de arquivos SVG está desativada, pois pode resultar em perda de qualidade ao dimensionar.',
                ],

            ],

        ],

    ],

    'key_value' => [

        'actions' => [

            'add' => [
                'label' => 'Adicionar linha',
            ],

            'delete' => [
                'label' => 'Excluir linha',
            ],

            'reorder' => [
                'label' => 'Reordenar linha',
            ],

        ],

        'fields' => [

            'key' => [
                'label' => 'Chave',
            ],

            'value' => [
                'label' => 'Valor',
            ],

        ],

    ],

    'markdown_editor' => [

        'tools' => [
            'attach_files' => 'Anexar arquivos',
            'blockquote' => 'Bloco de citação',
            'bold' => 'Negrito',
            'bullet_list' => 'Lista com marcadores',
            'code_block' => 'Bloco de código',
            'heading' => 'Título',
            'italic' => 'Itálico',
            'link' => 'Link',
            'ordered_list' => 'Lista ordenada',
            'redo' => 'Refazer',
            'strike' => 'Tachado',
            'table' => 'Tabela',
            'undo' => 'Desfazer',
        ],

    ],

    'radio' => [

        'boolean' => [
            'true' => 'Sim',
            'false' => 'Não',
        ],

    ],

    'repeater' => [

        'actions' => [

            'add' => [
                'label' => 'Adicionar em :label',
            ],

            'add_between' => [
                'label' => 'Inserir',
            ],

            'delete' => [
                'label' => 'Excluir',
            ],

            'clone' => [
                'label' => 'Clonar',
            ],

            'reorder' => [
                'label' => 'Mover',
            ],

            'move_down' => [
                'label' => 'Mover para baixo',
            ],

            'move_up' => [
                'label' => 'Mover para cima',
            ],

            'collapse' => [
                'label' => 'Recolher',
            ],

            'expand' => [
                'label' => 'Expandir',
            ],

            'collapse_all' => [
                'label' => 'Recolher todos',
            ],

            'expand_all' => [
                'label' => 'Expandir todos',
            ],

        ],

    ],

    'rich_editor' => [

        'actions' => [

            'attach_files' => [

                'label' => 'Carregar arquivo',

                'modal' => [

                    'heading' => 'Carregar arquivo',

                    'form' => [

                        'file' => [

                            'label' => [
                                'new' => 'Arquivo',
                                'existing' => 'Substituir arquivo',
                            ],

                        ],

                        'alt' => [

                            'label' => [
                                'new' => 'Texto alternativo',
                                'existing' => 'Alterar texto alternativo',
                            ],

                        ],

                    ],

                ],

            ],

            'custom_block' => [

                'modal' => [

                    'actions' => [

                        'insert' => [
                            'label' => 'Inserir',
                        ],

                        'save' => [
                            'label' => 'Salvar',
                        ],

                    ],

                ],

            ],

            'link' => [

                'label' => 'Editar',

                'modal' => [

                    'heading' => 'Link',

                    'form' => [

                        'url' => [
                            'label' => 'URL',
                        ],

                        'should_open_in_new_tab' => [
                            'label' => 'Abrir em uma nova aba',
                        ],

                    ],

                ],

            ],

        ],

        'no_merge_tag_search_results_message' => 'Nenhuma tag dinâmica encontrada.',

        'tools' => [
            'attach_files' => 'Anexar arquivos',
            'blockquote' => 'Bloco de citação',
            'bold' => 'Negrito',
            'bullet_list' => 'Lista com marcadores',
            'code_block' => 'Bloco de código',
            'custom_blocks' => 'Blocos',
            'h1' => 'Título',
            'h2' => 'Cabeçalho',
            'h3' => 'Subtítulo',
            'italic' => 'Itálico',
            'link' => 'Link',
            'merge_tags' => 'Tags dinâmicas',
            'ordered_list' => 'Lista ordenada',
            'redo' => 'Refazer',
            'strike' => 'Tachado',
            'subscript' => 'Subscrito',
            'superscript' => 'Sobrescrito',
            'underline' => 'Sublinhado',
            'undo' => 'Desfazer',
        ],

    ],

    'select' => [

        'actions' => [

            'create_option' => [

                'label' => 'Criar',

                'modal' => [

                    'heading' => 'Criar',

                    'actions' => [

                        'create' => [
                            'label' => 'Criar',
                        ],

                        'create_another' => [
                            'label' => 'Salvar e criar outro',
                        ],

                    ],

                ],

            ],

            'edit_option' => [

                'label' => 'Editar',

                'modal' => [

                    'heading' => 'Editar',

                    'actions' => [

                        'save' => [
                            'label' => 'Salvar',
                        ],

                    ],

                ],

            ],

        ],

        'boolean' => [
            'true' => 'Sim',
            'false' => 'Não',
        ],

        'loading_message' => 'Carregando...',

        'max_items_message' => 'Apenas :count item pode ser selecionado.|Apenas :count itens podem ser selecionados.',

        'no_search_results_message' => 'Nenhuma opção corresponde à sua pesquisa.',

        'placeholder' => 'Selecione uma opção',

        'searching_message' => 'Pesquisando...',

        'search_prompt' => 'Comece a digitar para pesquisar...',

    ],

    'tags_input' => [
        'placeholder' => 'Nova tag',
    ],

    'text_input' => [

        'actions' => [

            'hide_password' => [
                'label' => 'Esconder senha',
            ],

            'show_password' => [
                'label' => 'Mostrar senha',
            ],

        ],

    ],

    'toggle_buttons' => [

        'boolean' => [
            'true' => 'Sim',
            'false' => 'Não',
        ],

    ],

];
