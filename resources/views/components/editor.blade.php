<textarea 
    class="ckeditor form-control" 
    id="{{$editor_id ?? 'ckeditor'}}"
    {{isset($required) ? 'required' : ''}}
    name="{{$name}}">
    {{(str_contains(Request::path(), 'edit') ? $model->text : old($name))}}
</textarea>


<script src="{{asset('/assets/plugins/ckeditor/ckeditor.js')}}"></script>
<script>
    var options = {
        filebrowserImageBrowseUrl: '/admin/FileManager?type=Images',
        filebrowserImageUploadUrl: '/admin/FileManager/upload?type=Images&_token=',
        filebrowserBrowseUrl: '/admin/FileManager?type=Files',
        filebrowserUploadUrl: '/admin/FileManager/upload?type=Files&_token='
    };
    CKEDITOR.replace({{$editor_id ?? 'ckeditor'}}, options);
</script>
