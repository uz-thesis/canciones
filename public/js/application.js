var vidID = function() {
    return 'vidfile_' + Math.random().toString(36).substr(2, 15);
};

var vidSrc;

var uploadMusicVideo = function() {
    if ($('#uploadfile').get(0).files.length === 0) {
        alert("No video file selected. Please select file to continue.");
        return -1;
    }
    $.get(url + 'auth/getcustomtoken/admin', function(token, status) {
        firebase.auth().signInWithCustomToken(token).then(function() {
            $("#uploadprogress").parent().attr('class', 'progress');
            console.log('Notice: User successfully signed into Firebase.');

            // Create a root reference
            var storageRef = firebase.storage().ref();

            // File
            var file = $('#uploadfile').get(0).files[0];
            var fileName = file.name;
            var ext = fileName.split('.').pop();

            vidSrc = vidID() + '.' + ext;

            $('<input />').attr('type', 'hidden').attr('name', 'src').attr('value', 'videos/' + vidSrc).appendTo('#uploadform');

            // Upload file
            var uploadTask = storageRef.child('videos/' + vidSrc).put(file);

            // Listen for state changes, errors, and completion of the upload.
            uploadTask.on(firebase.storage.TaskEvent.STATE_CHANGED, // or 'state_changed'
                function(snapshot) {
                    // Get task progress, including the number of bytes uploaded and the total number of bytes to be uploaded
                    var progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100;
                    $('#uploadprogress').css('width', progress + '%');
                    $('#uploadprogress').attr('aria-valuenow', progress);
                    console.log('Upload is ' + progress + '% done');
                    switch (snapshot.state) {
                        case firebase.storage.TaskState.PAUSED: // or 'paused'
                            console.log('Upload is paused');
                            break;
                        case firebase.storage.TaskState.RUNNING: // or 'running'
                            console.log('Upload is running');
                            break;
                    }
                },
                function(error) {
                    // A full list of error codes is available at
                    // https://firebase.google.com/docs/storage/web/handle-errors
                    switch (error.code) {
                        case 'storage/unauthorized':
                            console.log('Error: Upload unauthorized.');
                            break;
                        case 'storage/canceled':
                            console.log('Error: Upload cancelled.');
                            break;
                        case 'storage/unknown':
                            console.log('Error: Unknown upload error.');
                            break;
                    }
                },
                function() {
                    // Upload completed successfully, now we can get the download URL
                    console.log('Notice: Video successfully uploaded.');
                    $('#uploadform').submit();
                });
        }).catch(function(error) {
            // Handle Errors here.
            var errorCode = error.code;
            var errorMessage = error.message;
            console.log('Error [' + error.code + ']: ' + error.message);
        });
    });
};

var firebaseSignOutUser = function() {
    firebase.auth().signOut().then(function() {
        console.log('Notice: User successfully signed out from Firebase.');
    }).catch(function(error) {
        console.log('Notice: User sign out failed.');
    });
};

var confirmDelete = function(vidName) {
    var result = confirm('Are you sure that you want to delete " ' + vidName + '"?');
    if (result) {
        return true;
    } else {
        return false;
    }
};
