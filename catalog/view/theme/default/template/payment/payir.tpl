<?php if ($error_warning) { ?>
<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?></div>
<?php } else { ?>
<div class="buttons">
  <div class="pull-right">
    <a id="button-confirm" href="<?php echo $action; ?>" target="_self" class="btn btn-primary"><?php echo $button_confirm; ?></a>
  </div>
</div>
<?php } ?>