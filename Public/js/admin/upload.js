 $.upload_img = function (sel) {
        $(document).on('change', sel + " > input[type='file']", function () {
            var objUrl = getObjectURL(this.files[0]);
            if (objUrl) {
                $(this).siblings('img').attr("src", objUrl);
            }
        });
    }
    
    //建立一個可存取到該file的url
    function getObjectURL(file) {
        var url = null;
        if (window.createObjectURL != undefined) { // basic
            url = window.createObjectURL(file);
        } else if (window.URL != undefined) { // mozilla(firefox)
            url = window.URL.createObjectURL(file);
        } else if (window.webkitURL != undefined) { // webkit or chrome
            url = window.webkitURL.createObjectURL(file);
        }
        return url;
    }