<script>
    const fileInput = $('#ImageBrowse').get(0);

    async function getBase64(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = () => resolve(reader.result.split(',')[1]);
            reader.onerror = error => reject(error);
        });
    }

    function storeUserImage(e) {
        e.preventDefault();

        if (fileInput.files && fileInput.files.length > 0) {
            const file = fileInput.files[0];
            getBase64(file).then(base64 => {
                console.log(file);
                // return;
                $.ajax({
                    type: 'PUT',
                    url: $(e.target).attr('action'),
                    data: {
                        image: base64,
                    },
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    success: function(data) {
                        console.log("success");
                        console.log(data);
                    },
                    error: function(data) {
                        console.log("error");
                        console.log(data);
                    }
                });
            }).catch(error => {
                console.error("Error converting to base64:", error);
            });
        } else {
            console.log('No file selected.');
        }
    }

    $('#imageUploadForm').on('submit', storeUserImage);
</script>
