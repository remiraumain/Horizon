Dropzone.autoDiscover = false;

$(document).ready(function () {
    var referenceList = new ReferenceList($('.js-reference-list'));

    initializeDropzone(referenceList);
});

/**
 * Images
 */
class ImageList
{
    constructor($element) {
        this.$element = $element;
        // this.sortable = Sortable.create(this.$element[0], {
        //     handle: '.drag-handle',
        //     animation: 150,
        //     onEnd: () => {
        //         $.ajax({
        //             url: this.$element.data('url') + '/reorder',
        //             method: 'POST',
        //             data: JSON.stringify(this.sortable.toArray())
        //         })
        //     }
        // });
        this.images = [];
        this.render();

        this.$element.on('click', '.js-image-delete', (event) => {
            this.handleImageDelete(event);
        });

        $.ajax({
            url: this.$element.data('url')
        }).then(data => {
            this.images = data;
            this.render();
        })
    }

    addImage(image) {
        this.images.push(image);
        this.render();
    }

    handleImageDelete(event) {
        const $li = $(event.currentTarget).closest('.list-group-item');
        const id = $li.data('id');
        $li.addClass('disabled');
        $.ajax({
            url: '/project/images/'+id,
            method: 'DELETE'
        }).then(() => {
            this.images = this.images.filter(image => {
                return image.id !== id;
            });
            this.render();
        });
    }

    render() {
        const itemsHtml = this.images.map(image => {
            return `
<li class="list-group-item" data-id="${image.id}">
    <i class="drag-handle fas fa-bars"></i>
    <span>
        <a href="/project/references/${image.id}/download"><span class="fa fa-download"></span></a>
        <button class="js-reference-delete"><i class="fas fa-trash-alt"></i></button>
    </span>
</li>
`
        });
        this.$element.html(itemsHtml.join(''));
    }
}

(function () {
    var imageCatcher = document.getElementById('image-catcher');
    var imageInput = document.getElementById('image-input');
    var fileListDisplay = document.getElementById('file-list-display');

    var fileList = [];
    var renderFileList, sendFile;

    imageCatcher.addEventListener('submit', function (evnt) {
        evnt.preventDefault();
        fileList.forEach(function (file) {
            sendFile(file);
        });
    });

    imageInput.addEventListener('change', function (evnt) {
        fileList = [];
        for (var i = 0; i < imageInput.files.length; i++) {
            fileList.push(imageInput.files[i]);
        }
        renderFileList();
    });

    renderFileList = function () {
        fileListDisplay.innerHTML = '';
        fileList.forEach(function (file, index) {
            var fileDisplayEl = document.createElement('p');
            fileDisplayEl.innerHTML = (index + 1) + ': ' + file.name;
            fileListDisplay.appendChild(fileDisplayEl);
        });
    };

    sendFile = function (file) {
        var formData = new FormData();
        var request = new XMLHttpRequest();

        formData.set('file', file);
        request.open("POST", 'https://jsonplaceholder.typicode.com/photos');
        request.send(formData);
    };
})();


// todo - use Webpack Encore so ES6 syntax is transpiled to ES5
class ReferenceList
{
    constructor($element) {
        this.$element = $element;
        this.sortable = Sortable.create(this.$element[0], {
            handle: '.drag-handle',
            animation: 150,
            onEnd: () => {
                $.ajax({
                    url: this.$element.data('url') + '/reorder',
                    method: 'POST',
                    data: JSON.stringify(this.sortable.toArray())
                })
            }
        });
        this.references = [];
        this.render();

        this.$element.on('click', '.js-reference-delete', (event) => {
            this.handleReferenceDelete(event);
        });
        this.$element.on('blur', '.js-edit-filename', (event) => {
            this.handleReferenceEditFilename(event);
        });

        $.ajax({
            url: this.$element.data('url')
        }).then(data => {
            this.references = data;
            this.render();
        })
    }

    addReference(reference) {
        this.references.push(reference);
        this.render();
    }

    handleReferenceDelete(event) {
        const $li = $(event.currentTarget).closest('.list-group-item');
        const id = $li.data('id');
        $li.addClass('disabled');
        $.ajax({
            url: '/project/references/'+id,
            method: 'DELETE'
        }).then(() => {
            this.references = this.references.filter(reference => {
                return reference.id !== id;
            });
            this.render();
        });
    }

    handleReferenceEditFilename(event) {
        const $li = $(event.currentTarget).closest('.list-group-item');
        const id = $li.data('id');
        const reference = this.references.find(reference => {
            return reference.id === id;
        });
        reference.originalFilename = $(event.currentTarget).val();

        $.ajax({
            url: '/project/references/'+id,
            method: 'PUT',
            data: JSON.stringify(reference)
        });
        // TODO : handle error rendering
    }

    render() {
        const itemsHtml = this.references.map(reference => {
            return `
<li class="list-group-item" data-id="${reference.id}">
    <i class="drag-handle fas fa-bars"></i>
    <input type="text" value="${reference.originalFilename}" class="js-edit-filename">
    <span>
        <a href="/project/references/${reference.id}/download"><span class="fa fa-download"></span></a>
        <button class="js-reference-delete"><i class="fas fa-trash-alt"></i></button>
    </span>
</li>
`
        });
        this.$element.html(itemsHtml.join(''));
    }
}

/**
 *
 * @param {ReferenceList} referenceList
 */
function initializeDropzone(referenceList) {
    var formElement = document.querySelector('.js-reference-dropzone');
    if (!formElement) {
        return;
    }
    var dropzone = new Dropzone(formElement, {
        paramName: 'reference',
        init: function () {
            this.on('success', function (file, data) {
                referenceList.addReference(data);
            });
            this.on('error', function (file, data) {
                if (data.detail) {
                    this.emit('error', file, data.detail)
                }
            });
        }
    });
}