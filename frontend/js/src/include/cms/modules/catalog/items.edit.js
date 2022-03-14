/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */
'use strict';

import {createApp, ref, reactive, watch, onMounted} from 'vue';
import 'jquery-confirm';
// noinspection ES6CheckImport
import {createTree} from 'jquery.fancytree';
import 'jquery.fancytree/dist/modules/jquery.fancytree.table';
import 'jquery.fancytree/dist/skin-win7/ui.fancytree.css';
import FlashMessenger from 'include/cms/plugins/flashmessenger';
import Sortable from 'sortablejs';
import {Tab} from 'bootstrap';
import {filterFormValues} from "lib/common";

window.addEventListener('load', function () {

    const ItemsEdit = {
        setup() {
            const pictures = ref([]);
            const related = ref([]);
            const upload_available = ref(false);
            const input = reactive({
                categories: [],
                code: null,
                name: null
            });

            onMounted(() => {
                // Инициализация дерева категорий
                createTree('#categoriesTree', {
                    extensions: ['table'],
                    source: {
                        url: ('/catalog/categories/gettree')
                    },
                    checkbox: true,
                    clickFolderMode: 3,
                    selectMode: 2,
                    activeVisible: true,
                    table: {
                        indentation: 20,
                        checkboxColumnIdx: 0,
                        nodeColumnIdx: 1
                    },
                    // Заполнение начальных выбранных категорий
                    init: (event, data) => {
                        input.categories.forEach(category => {
                            let node = data.tree.getNodeByKey(category);
                            node.setSelected(true);
                            node.setActive(true);
                            node.setExpanded(true);
                        });
                    },

                    // Обработка выбора категории
                    select: (event, data) => {
                        input.categories = [];
                        data.tree.getSelectedNodes().forEach(node => {
                            input.categories.push(node.key);
                        });
                    }
                });

                // Заполняем начальные значения формы
                for (let [field, value] of Object.entries(init_data.main)) {
                    input[field] = value;
                }

                // Загрузка списка прикрепленных изображений
                pictures.value = init_data.pictures;

                // Загрузка списка рекомендуемых товаров
                related.value = init_data.related;

                // Инициализация открытия вкладки по ссылке
                let hash = document.location.hash;
                if (hash) {
                    let activeTab = new Tab(hash + '-tab');
                    activeTab.show()
                }
            });

            /**
             * Обработка отправки формы
             *
             * @param e
             * @returns {boolean}
             */
            const submitForm = e => {
                if (!input.categories.length) {
                    $.alert({
                        title: 'Ошибка добавления товара',
                        content: 'Выберите категории для привязки товара!',
                        type: 'red'
                    });
                    let categoriesTab = new Tab('#categories-tab');
                    categoriesTab.show()
                    e.preventDefault();
                } else {
                    return true;
                }
            }

            /**
             * Генерация символьной ссылки
             */
            const generateCode = () => {
                let formData = new FormData;
                formData.append('source', input.name);
                fetch('/admin/params/system/generateurl', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        input.code = data.url;
                    });
            }

            /**
             * Добавление нового торгового предложения
             */
            const addOffer = () => {
                location.href = '/catalog/offers/add/' + init_data.main.id;
            }

            /**
             * Удаление изображения
             *
             * @param id
             */
            const deletePicture = id => {
                $.confirm({
                    content: 'Удалить выбранное изображение?',
                    title: 'Подтверждение действия',
                    type: 'red',
                    columnClass: 'medium',
                    buttons: {
                        yes: {
                            text: 'Да',
                            btnClass: 'btn-red',
                            action: () => {
                                fetch(`/catalog/pictures/delete/${id}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.code === 1) {
                                            pictures.value = data.pictures;
                                        }
                                    });
                            }
                        },
                        no: {
                            text: 'Нет'
                        }
                    }
                });
            }

            /**
             * Загрузка новых прикрепленных изображений на сервер
             */
            const uploadPictures = () => {
                upload_available.value = false;
                /** @type {object} picturesInput */
                let picturesInput = document.querySelector('#pictures-input');
                if (picturesInput.files.length) {
                    let formData = new FormData;
                    for (let file of picturesInput.files) {
                        formData.append('picture[]', file);
                    }

                    fetch(`/catalog/pictures/add/${init_data.main.id}`, {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            switch (data.code) {
                                case 0:
                                    FlashMessenger.message('Ошибка загрузки изображений', 'error');
                                    break;
                                case 1:
                                    pictures.value = data.pictures;
                                    FlashMessenger.message('Изображения успешно загружены');
                                    break;
                            }
                            upload_available.value = true;

                        });
                }
            }

            /**
             * Добавление рекомендуемого товара
             */
            const addRelated = () => {
                $.confirm({
                    title: 'Добавление рекомендованого товара',
                    content: `
                        <form>
                            <div class="form-group">
                                <input type="text" placeholder="Артикул" class="article form-control" required />
                            </div>
                        </form>`,
                    buttons: {
                        formSubmit: {
                            text: 'Добавить',
                            btnClass: 'btn-blue',
                            action: function () {
                                let article = this.$content.find('.article').val();
                                if (!article) {
                                    $.alert('Введите артикул');
                                    return false;
                                }

                                let formData = new FormData;
                                formData.append('item', init_data.main.id);
                                formData.append('article', article);

                                fetch('/catalog/items/add-related', {
                                    method: 'POST',
                                    body: formData
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.code === 1) {
                                            related.value = data.related;
                                        } else {
                                            $.alert('Наименования с данным штрих-кодом не найдено в базе товаров');
                                        }

                                    });
                            }
                        },
                        cancel: {
                            text: 'Отмена',
                        }
                    }
                });
            }

            /**
             * Удаление привязанного товара
             *
             * @param {number} id
             */
            const deleteRelated = id => {
                $.confirm({
                    content: 'Удалить рекомендуемое наименование?',
                    title: 'Подтверждение действия',
                    type: 'red',
                    columnClass: 'medium',
                    buttons: {
                        yes: {
                            text: 'Да',
                            btnClass: 'btn-red',
                            action: () => {
                                fetch(`/catalog/items/delete-related/${id}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.code === 1) {
                                            related.value = data.related;
                                        }
                                    });
                            }
                        },
                        no: {
                            text: 'Нет'
                        }
                    }
                });
            }

            /**
             * Проверка условаия выбора файлов
             *
             * @param event
             */
            const checkPicturesSelected = event => {
                if (event.target.files.length > 5) {
                    $.alert({
                        title: 'Ошибка добавления изображений к товару',
                        content: 'За один раз к товару можно прикрепить максимально 5 изображений',
                        type: 'red'
                    });
                    event.target.value = '';
                }
                upload_available.value = !!event.target.files.length;
            }

            /**
             * Инициализация сортировки изображений
             */
            const initPicturesSorable = () => {
                Sortable.create(document.querySelector('.item-edit-pictures'), {
                    onEnd: function() {
                        let formData = new FormData;
                        formData.append('listValues', this.toArray());
                        fetch('/catalog/pictures/reorder', {
                            method: 'POST',
                            body: formData
                        });
                    }
                });
            }

            watch(pictures, initPicturesSorable);

            return {
                input,
                pictures,
                related,
                upload_available,
                submitForm,
                generateCode,
                uploadPictures,
                deletePicture,
                addOffer,
                addRelated,
                deleteRelated,
                checkPicturesSelected
            }
        }
    }

    filterFormValues('#itemForm');
    createApp(ItemsEdit).mount('#itemsEditApp');
});