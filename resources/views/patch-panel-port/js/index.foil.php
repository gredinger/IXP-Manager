<script>

$(document).ready(function(){

    // set global variable for file uploads:
    window.loadscript = false;

    pagination = true;
    <?php if($t->patchPanel): ?>
        // unless we have a single patch panel in which case we disable:
        pagination = false;
    <?php endif; ?>

    $('#patch-panel-port-list').DataTable({
        "paging":   pagination,
        "autoWidth": false,
        "columnDefs": [{
            "targets": [ 0 ],
            "visible": false,
            "searchable": false,
        }],
        "order": [[ 0, "asc" ]]
    });

    $( "a[id|='edit-notes']" ).on( 'click', function(e){
        e.preventDefault();
        var pppid = (this.id).substring(11);
        popup( pppid, 'edit-notes' );
    });


});

function setNotesTextArea( pppId, input ) {
    val_textarea = $('#'+input).text();
    default_val = '## <?= date("Y-m-d" ).' - '.$t->user->getUsername()?> \n\n';
    pos = default_val.length + ($('#'+input).val().length - $('#'+input).text().length);


    if(val_textarea == ''){
        $('#'+input).setCursorPosition(pos);
        $('#'+input).text(default_val);
    } else {
        if($('#'+input).text() != default_val){
            if(input == 'notes'){
                if(!window.new_notes_set){
                    $('#'+input).text(default_val+'\n\n'+val_textarea);
                    window.new_notes_set = true;
                    $('#'+input).setCursorPosition(pos);
                }
            } else {
                if(!window.new_private_notes_set){
                    $('#'+input).text(default_val+'\n\n'+val_textarea);
                    window.new_private_notes_set = true;
                    $('#'+input).setCursorPosition(pos);
                }
            }
        }
    }

    pos = default_val.length + ($('#'+input).val().length - $('#'+input).text().length);
    $('#'+input).setCursorPosition(pos);
}

function checkTextArea(pppId,input){
    if($('#'+input).text() == $('#'+input).val()){
    $('#'+input).text($('#'+input+'_'+pppId).val());
    if(input == 'notes'){
    window.new_notes_set = false;
    }
    else{
    window.new_private_notes_set = false;
    }

    }
}

function popup( pppId, action ) {
    var new_notes_set = false;
    var html = "";

    ajaxActionPatchPanelPort( pppId, action, function( ppp, action ) {

        if( action != 'edit-notes' ) {
            html = "<p>Consider adding details to the notes such as a internal ticket reference to the cease request / whom you have been dealing with / expected cease date / etc..</p><br/>";
        }

        // onblur='checkTextArea(" + pppId + ",\"notes\")' onfocus='setNotesTextArea(" + pppId + ",\"notes\")' onclick='setNotesTextArea(" + pppId + ",\"notes\")'
        // onblur='checkTextArea(" + pppId + ",\"private_notes\")' onfocus='setNotesTextArea(" + pppId + ",\"private_notes\")' onclick='setNotesTextArea(" + pppId + ",\"private_notes\")'

        html += 'Public Notes:  <textarea id="notes"         rows="8" class="bootbox-input bootbox-input-textarea form-control">' + ppp.notes        + "</textarea><br>" +
                'Private Notes: <textarea id="private-notes" rows="8" class="bootbox-input bootbox-input-textarea form-control">' + ppp.privateNotes + "</textarea>";

        if( action == 'set-connected' ) {
            if( ppp.switchPortId ) {
                html += '<br><br><span>Update Physical Port State To: </span><select id="pi-status">';

                var haveCurrentState = false;
                <?php foreach( $t->physicalInterfaceStatesSubSet as $i => $s ): ?>

                    html += '<option <?= $i == \Entities\PhysicalInterface::STATUS_QUARANTINE ? 'selected="selected"' : '' ?> value="<?= $i ?>"><?= $s ?>';

                    if( <?= $i ?> == ppp.switchPort.physicalInterface.statusId ) {
                        haveCurrentState = true;
                        html += " (current state)";
                    }

                    html += '</option>';

                <?php endforeach ;?>

                if( !haveCurrentState ) {
                    html += '<option value="' + ppp.switchPort.physicalInterface.statusId + '">' + ppp.switchPort.physicalInterface.status + ' (current state)</option>';
                }

                html += "</select>";
            }
        }

        var dialog = bootbox.dialog({
            message: html,
            title: "Notes",
            buttons: {
                cancel: {
                    label: '<i class="fa fa-times"></i> Cancel',
                    callback: function () {
                        $('.bootbox.modal').modal('hide');
                        return false;
                    }
                },
                confirm: {
                    label: '<i class="fa fa-check"></i> Confirm',
                    callback: function () {
                        notes         = $('#notes').val();
                        private_notes = $('#private_notes').val();
                        if( ppp.switchPortId ) {
                            pi_status = $('#pi-status').val();
                        } else {
                            pi_status = null;
                        }

                        $.ajax({
                            url: "<?= url('patch-panel-port/set-notes/')?>",
                            data: {
                                pppId: pppId,
                                notes: notes,
                                private_notes: private_notes,
                                pi_status: pi_status,
                                only_note: only_note
                            },
                            type: 'GET',
                            dataType: 'JSON',
                            success: function (data) {
                                if (data.success) {
                                    if (only_note) {
                                        $('.bootbox.modal').modal('hide');
                                        location.reload();
                                    } else {
                                        document.location.href = url;
                                    }

                                    return true;
                                } else {
                                    $('.bootbox.modal').modal('hide');
                                    return false;
                                }
                            }
                        });
                    }
                }
            }
        });

        dialog.init(function () {
            window.new_notes_set = false;
            window.new_private_notes_set = false;
        });

    }); // ajaxGetPatchPanelPortDetail()
}

function ajaxActionPatchPanelPort( pppid, action, handleData ) {
    return $.ajax( "<?= url('api/v4/patch-panel-port') ?>/" + pppid + "/1" )   // + "/1" => deep array to include subobjects
        .done( function( data ) {
            handleData( data, action );
        })
        .fail( function() {
            throw new Error("Error running ajax query for patch-panel-port/$id");
        });
}


    function uploadPopup(pppId){
        html = "<form id='upload' method='post' action='<?= url('/patch-panel-port/upload-file' )?>/"+pppId+"' enctype='multipart/form-data'> <div id='drop'>Drop Files Here &nbsp;<a class='btn btn-success'><i class='glyphicon glyphicon-upload'></i> Browse</a> <br/><span class='info'> (max size 50MB) </span><input type='file' name='upl' multiple /> </div> <ul><!-- The file uploads will be shown here --> </ul><input type='hidden' name='_token' value='<?php echo csrf_token(); ?>'> </form>";

        var dialog = bootbox.dialog({
            message: html,
            title: "Files Upload (Files will be public by default)",
            onEscape: function() {
                location.reload();
            },
            buttons: {
                cancel: {
                    label: '<i class="fa fa-times"></i> Close',
                    callback: function () {
                        $('.bootbox.modal').modal('hide');
                        location.reload();
                        return false;
                    }
                },
            }
        });

        dialog.init(function(){
            $.getScript( "js/draganddrop/jquery.fileupload.js", function( data, textStatus, jqxhr ) {});
            $.getScript( "js/draganddrop/jquery.iframe-transport.js", function( data, textStatus, jqxhr ) {});
            $.getScript( "js/draganddrop/jquery.knob.js", function( data, textStatus, jqxhr ) {});
            $.getScript( "js/draganddrop/jquery.ui.widget.js", function( data, textStatus, jqxhr ) {});
            $.getScript( "js/draganddrop/script.js", function( data, textStatus, jqxhr ) {});
            window.loadscript = true;
        });

        return false;
    }

    function deleteFile(idFile,idPPP){
        $.ajax({
            url: "<?= url('patch-panel-port/delete-file/')?>",
            data: {idFile: idFile, idPPP: idPPP},
            type: 'GET',
            dataType: 'JSON',
            success: function (data) {
                if(data.success){
                    $('#file_'+idFile).fadeOut( "medium", function() {
                        $('#file_'+idFile).remove();
                    });
                } else {
                    $('#message_'+idFile).removeClass('success').addClass('error').html('Delete error : '+data.message);
                    $('#delete_'+idFile).remove();
                }
            }
        });
    }

    function changePrivateFile(idFile,idPPP){
        $.ajax({
            url: "<?= url('patch-panel-port/change-private-file/')?>",
            data: {idFile: idFile, idPPP: idPPP},
            type: 'GET',
            dataType: 'JSON',
            success: function (data) {
                if(data.success){
                    $('#privateMessage_'+idFile).html(' / <i class="success">'+data.message+'</i>');
                    if($('#private_'+idFile).hasClass('fa-lock')){
                        $('#private_'+idFile).removeClass('fa-lock');
                        $('#private_'+idFile).addClass('fa-unlock');
                    } else {
                        $('#private_'+idFile).removeClass('fa-unlock');
                        $('#private_'+idFile).addClass('fa-lock');
                    }

                } else {
                    $('#privateMessage_'+idFile).html(' / <i class="error"> '+data.message+'</i>');
                    $('#private_'+idFile).remove();
                }

            }
        });
    }

</script>
