<div id="contentsource">
    <div class="icon icon_<?php echo strtolower($_GET['type']); ?>"></div>
    <dl>
        <dt>Source:</dt>
            <dd><?php echo $_GET['name']; ?></dd>
        <dt>Channel type:</dt>
            <dd><?php echo strtolower($_GET['type']); ?></dd>
        <dt>Source veracity:</dt>
            <dd><?php echo($_GET['score'] == "null" ? "Not yet rated" : $_GET['score']); ?></dd>
        <dt>Link:</dt>
<<<<<<< HEAD
            <dd><?php echo $_GET['contentlink']; ?></dd>
=======
            <dd><a href="<?php echo $_GET['contentlink']; ?>" target="_blank"><?php echo $_GET['contentlink']; ?></a></dd>
>>>>>>> df035edb6e6d06904c646f0d30bd8ae4f0745858
    </dl>
</div>