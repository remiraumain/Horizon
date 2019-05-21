Dropzone.autoDiscover = false;

$(document).ready(function () {
    var referenceList = new ReferenceList($('.js-reference-list'));
    var imageList = new ImageList($('.js-image-list'));

    initializeDropzone(referenceList);
});

class ImageList
{
    constructor($element) {
        this.$element = $element;
        this.images = [];
        this.render();

        this.$element.on('click', '.js-image-delete', (event) => {
            this.handleImageDelete(event);
        });

        $('.form-image').on('submit', (event) => {
            event.preventDefault();
            this.addImage(event);
        });

        $.ajax({
            url: this.$element.data('url')
        }).then(data => {
            this.images = data;
            this.render();
        })
    }

    addImage(event, form) {
        var url = $(event.currentTarget)[0];
        $.ajax({
            url: url.action,
            method: 'POST',
            data: new FormData(url),
            contentType:false,
            processData:false,
            cache:false,
            dataType:"json",
            error:function(err){
                if (!$(".alert").length)
                {
                    $('<div class="alert alert-danger"></div>').insertAfter('nav');
                }
                $(".alert").append(err.responseJSON.detail);
            },
            success:function(data){
                if ($(".alert").length)
                {
                    $(".alert").toggleClass('in out fade');
                    setTimeout(function (){$('.alert').remove()}, 850);
                }
                this.images.push(data);
            }.bind(this),
            complete:function(){
                console.log("Request finished.");
                this.render();
            }.bind(this)
        })
    }

    handleImageDelete(event) {
        const $li = $(event.currentTarget).closest('.list-group-item');
        const id = $li.data('id');
        $li.addClass('disabled');
        $.ajax({
            url: '/project/images/'+id,
            method: 'DELETE',
            error:function(err){
                if (!$(".alert").length)
                {
                    $('<div class="alert alert-danger"></div>').insertAfter('nav');
                }
                $(".alert").append(err.responseJSON.detail);
            },
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
<li class="list-group-item project-image-list" data-id="${image.id}">
    <img class="project-image" src="/uploads/project_image/${image.filename}" alt="project's image">
    <span>
        <button class="js-image-delete"><i class="fas fa-times-circle project-image-delete"></i></button>
    </span>
</li>
`
        });
        this.$element.html(itemsHtml.join(''));
    }
}

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
        timeout: 180000,
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