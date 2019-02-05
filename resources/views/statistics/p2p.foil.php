<?php
    /** @var Foil\Template\Template $t */
    $this->layout( 'layouts/ixpv4' );
?>

<?php $this->section( 'page-header-preamble' ) ?>

<?php if( Auth::check() && Auth::user()->isSuperUser() ): ?>


    <a href="<?= route( 'customer@overview', [ 'id' => $t->c->getId() ] ) ?>" >
        <?= $t->c->getFormattedName() ?>
    </a>

    /

    <a href="<?= route( 'statistics@member', [ 'id' => $t->c->getId() ] ) ?>" >
        Statistics
    </a>
    /
    <a href="<?= route( 'statistics@member', [ 'id' => $t->c->getId() ] ) ?>" >
        Peer to Peer Graphs
    </a>
    (<?= $t->srcVli->getIPAddress( $t->protocol ) ? $t->srcVli->getIPAddress( $t->protocol )->getAddress() : 'No IP' ?>
        / <?= IXP\Services\Grapher\Graph::resolveCategory( $t->category ) ?>
        / <?= IXP\Services\Grapher\Graph::resolvePeriod( $t->period ) ?>
        / <?= IXP\Services\Grapher\Graph::resolveProtocol( $t->protocol ) ?>
    )


<?php else: ?>

    Peer to Peer Graphs :: <?= $t->c->getFormattedName() ?>



<?php endif; ?>

<?php $this->append() ?>

<?php if( Auth::check() && !Auth::user()->isSuperUser() ): ?>
    <?php $this->section( 'page-header-postamble' ) ?>

        <?php if( $t->grapher()->canAccessAllCustomerGraphs() ): ?>

                <a class="btn btn-outline-secondary" href="<?= route( 'statistics@member', [ 'id' => $t->c->getId() ] ) ?>">All Ports</a>

        <?php endif; ?>

    <?php $this->append() ?>
<?php endif; ?>


<?php $this->section('content') ?>

<div class="row">

    <div class="col-md-12">

        <?= $t->alerts() ?>

            <nav id="filter-row" class="navbar navbar-expand-lg navbar-light bg-light mb-4 shadow-sm">

                <div class="navbar-header">
                    <a class="navbar-brand">P2P Graphs</a>
                </div>
                <div class="collapse navbar-collapse" id="navbarNavDropdown">
                    <ul class="navbar-nav">

                        <form class="navbar-form navbar-left form-inline" action="<?= route( 'statistics@p2p', [ 'cid' => $this->c->getId() ] ) ?>" method="post">

                            <li class="nav-item mr-2">
                                <div class="nav-link d-flex ">
                                    <label for="select_network" class="mr-2">Interface:</label>
                                    <select id="select_network" name="svli" class="form-control">
                                        <?php foreach( $t->srcVlis as $id => $vli ): ?>
                                            <option value="<?= $id ?>" <?php if( $t->srcVli->getId() == $id ): ?> selected <?php endif; ?>  >
                                                <?= $vli->getVlan()->getName() ?>
                                                :: <?= $vli->getIPAddress( $t->protocol ) ? $vli->getIPAddress( $t->protocol )->getAddress() : 'No IP - VLI ID: ' . $vli->getId() ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </li>

                            <?php if( $t->showGraphs ): ?>

                                <li class="nav-item mr-2">
                                    <div class="nav-link d-flex ">
                                        <label for="select_category" class="mr-2">Category:</label>
                                        <select id="select_category" name="category" class="form-control">
                                            <?php foreach( IXP\Services\Grapher\Graph::CATEGORIES_BITS_PKTS_DESCS as $cvalue => $cname ): ?>
                                                <option value="<?= $cvalue ?>" <?php if( $t->category == $cvalue ): ?> selected <?php endif; ?>  >
                                                    <?= $cname ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </li>

                                <li class="nav-item mr-2">
                                    <div class="nav-link d-flex ">
                                        <label for="select_period" class="mr-2">Period:</label>
                                        <select id="select_period" name="period" class="form-control">
                                            <?php foreach( IXP\Services\Grapher\Graph::PERIOD_DESCS as $pvalue => $pname ): ?>
                                                <option value="<?= $pvalue ?>" <?php if( $t->period == $pvalue ): ?> selected <?php endif; ?>  >
                                                    <?= $pname ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </li>

                            <?php endif; ?>

                            <li class="nav-item mr-2">
                                <div class="nav-link d-flex ">
                                    <label for="select_protocol" class="mr-2">Protocol:</label>
                                    <select id="select_protocol" name="protocol" class="form-control">
                                        <?php foreach( IXP\Services\Grapher\Graph::PROTOCOL_REAL_DESCS as $pvalue => $pname ): ?>
                                            <?php if( $t->srcVli->getVlan()->getPrivate() || $t->srcVli->isIPEnabled( $pvalue ) ): ?>
                                                <option value="<?= $pvalue ?>" <?php if( $t->protocol == $pvalue ): ?> selected <?php endif; ?>  >
                                                    <?= $pname ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </li>

                            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                            <input class="btn btn-outline-secondary" type="submit" name="submit" value="Submit" />

                            <?php if( $t->showGraphsOption ): ?>
                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <input class="btn btn-outline-secondary" type="submit" name="submit" value="<?= $t->showGraphs ? 'Hide' : 'Show' ?> Graphs" />
                            <?php endif; ?>

                        </form>
                    </ul>
                </div>

            </nav>

    </div>

</div>


<?php
    /** @var $dstVlis Entities\VlanInterface[] */
    $dstVlis = $t->dstVlis;
    foreach( $dstVlis as $id => $dvli ) {
        if( !$t->srcVli->getVlan()->getPrivate() && !$dvli->isIPEnabled( $t->protocol ) ) {
            unset( $dstVlis[ $id ] );
        }
    }

    $cnt = 0;
    $total = count( $dstVlis );
    $firstColComplete = false;
?>



<?php if( !$t->showGraphs ): ?>

    <div class="row">

        <div class="col-md-6">

            <ul>

                <?php
                    foreach( $dstVlis as $dvli ):
                ?>

                    <li>
                        <a href="<?= route( 'statistics@p2p', [ 'cid' => $t->c->getId() ] )
                            . '?svli='     . $t->srcVli->getId()
                            . '&dvli='     . $dvli->getId()
                            . '&category=' . $t->category
                            . '&period='   . $t->period
                            . '&protocol=' . $t->protocol
                        ?>">
                            <?= $dvli->getVirtualInterface()->getCustomer()->getFormattedName() ?>
                        </a>
                    </li>

                    <?php $cnt++; ?>
                    <?php if( !$firstColComplete && $cnt > ( $total / 2 ) ): ?>
                        </ul>
                        </div>
                        <div class="col-md-6">
                        <ul>
                        <?php $firstColComplete = true; ?>
                    <?php endif; ?>

                <?php endforeach; ?>

            </ul>
        </div>
    </div>

<?php else: /* if( !$t->showGraphs ) */ ?>

    <div class="row">

        <?php foreach( $dstVlis as $dvli ): ?>

            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6">

                <div class="well">

                    <h4>
                        <?= $dvli->getVirtualInterface()->getCustomer()->getFormattedName() ?> :: <?= $dvli->getIPAddress( $t->protocol ) ? $dvli->getIPAddress( $t->protocol )->getAddress() : 'No IP' ?>
                    </h4>

                    <p>
                        <br />
                        <a href="<?= route( 'statistics@p2p', [ 'cid' => $t->c->getId() ] )
                            . '?svli='     . $t->srcVli->getId()
                            . '&dvli='     . $dvli->getId()
                            . '&category=' . $t->category
                            . '&period='   . $t->period
                            . '&protocol=' . $t->protocol
                        ?>">
                            <img class="img-responsive" src="<?= $t->graph->setDestinationVlanInterface( $dvli, false )->setType('png')->url() ?>">
                        </a>
                    </p>
                </div>
            </div>

        <?php endforeach; ?>

    </div>

<?php endif; ?>

<?php $this->append() ?>



<?php $this->section( 'scripts' ) ?>
<?= $t->insert( 'statistics/js/p2p' ); ?>
<?php $this->append() ?>


