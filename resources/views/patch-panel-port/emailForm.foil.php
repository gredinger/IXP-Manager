<?php $this->layout('layouts/ixpv4') ?>

<?php $this->section('title') ?>
    <a href="<?= url('patch-panel-port/list/patch-panel/'.$t->patchPanelPort->getPatchPanel()->getId())?>">Patch Panel Port</a>
<?php $this->append() ?>

<?php $this->section('page-header-postamble') ?>
    <li>Email : <?= $t->patchPanelPort->getName()?></li>
<?php $this->append() ?>

<?php $this->section('content') ?>

    <?= Former::open()->method('POST')
        ->action(url('patch-panel-port/sendEmail'))
        ->customWidthClass('col-sm-10')
        ->addClass('col-md-10');
    ?>
        <?= Former::text('email_to')
            ->label('To')
            ->help('help text');
        ?>

        <?= Former::text('email_cc')
            ->label('CC')
            ->help('CC');
        ?>

        <?= Former::text('email_bcc')
            ->label('BCC')
            ->help('help text');
        ?>

        <?= Former::text('email_subject')
            ->label('Subject')
            ->help('help text');
        ?>

        <?php if($t->email_type != \Entities\PatchPanelPort::EMAIL_LOA): ?>
            <?= Former::checkbox('loa')
                ->label('Attach LoA as a PDF')
                ->check(true)
            ?>
        <?php endif; ?>


        <?= Former::textarea('email_text')
            ->label('Email')
            ->rows(30)
            ->style('width:100%')
            ->help('help text');
        ?>

        <?=Former::actions( Former::primary_submit('Send Email'),
            Former::default_link('Cancel')->href(url('patch-panel-port/list/patch-panel/'.$t->patchPanelPort->getPatchPanel()->getId())),
            Former::success_button('Help')->id('help-btn')
        );?>

        <?= Former::hidden('email_type')
            ->value($t->email_type)
        ?>

        <?= Former::hidden('patch_panel_port_id')
            ->value($t->patchPanelPort->getId())
        ?>
    <?= Former::close() ?>

<?php $this->append() ?>

<?php $this->section('scripts') ?>
    <link rel="stylesheet" type="text/css" href="<?= asset('css/bootstrap-tagsinput.css') ?>" />
    <script type="text/javascript" src="<?= asset('/js/bootstrap-tagsinput.js') ?>"></script>
    <script>
        $(document).ready(function(){

            $('#email_bcc').tagsinput();

            $('#email_bcc').on('beforeItemAdd', function(event) {
                event.cancel = check(event.item);
            });

            $('#email_cc').tagsinput();

            $('#email_cc').on('beforeItemAdd', function(event) {
                event.cancel = check(event.item);
            });

            $('#email_to').tagsinput({
                tagClass: 'label label-primary'
            });

            $('#email_to').on('beforeItemAdd', function(event) {
                event.cancel = check(event.item);
            });
        });

        function check(text){
            var filter = /^[\w-.+]+@[a-zA-Z0-9.-]+.[a-zA-z0-9]{2,4}$/;
            if (!filter.test(text)) {
               return true;
            }
            else{
                return false;
            }
        }

    </script>
<?php $this->append() ?>