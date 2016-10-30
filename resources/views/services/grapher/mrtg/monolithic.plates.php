<?php /*
    MRTG Configuration Templates

    Please see: https://github.com/inex/IXP-Manager/wiki/MRTG---Traffic-Graphs

    You should not need to edit these files - instead use your own custom skins. If
    you can't effect the changes you need with skinning, consider posting to the mailing
    list to see if it can be achieved / incorporated.

    Skinning: https://github.com/inex/IXP-Manager/wiki/Skinning
*/ ?>

<?php $this->insert('skin::services/grapher/mrtg/header'); ?>
<?php $this->insert('skin::services/grapher/mrtg/custom-header'); ?>

<?php $this->insert('skin::services/grapher/mrtg/aggregates',        ['data' => $data, 'ixp' => $ixp]); ?>
<?php $this->insert('skin::services/grapher/mrtg/switch-aggregates', ['data' => $data]); ?>

<?php $this->insert('skin::services/grapher/mrtg/trunks',            ['snmppasswd' => $snmppasswd]); ?>

<?php $this->insert('skin::services/grapher/mrtg/member-ports',      ['data' => $data]); ?>

<?php $this->insert('skin::services/grapher/mrtg/custom-footer'); ?>
