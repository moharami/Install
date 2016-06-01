<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <?= $this->Html->meta(
        'favicon.ico',
        'Install./img/logo/spider/spider.ico',
        ['type' => 'icon']
    ) ?>
    
    <title>
        <?= $title_for_layout; ?> - <?= __d('spider', 'Spider'); ?>
    </title>

    <?= $this->Html->css('Install.bootstrap.min.css'); ?>

    <!-- Custom CSS -->
    
    <?= $this->Html->css('Install.freelancer.css'); ?>
    <?= $this->Html->css('Install.custom.css'); ?>

    <!-- Custom Fonts -->
    <?= $this->Html->css('Install.../font-awesome/css/font-awesome.min.css'); ?>

</head>

<body id="page-top" class="index">
<?= $this->element('Install.admin/header'); ?>



<div class="container">
    <?= $this->Flash->render() ?>
    <?= $this->fetch('content') ?>
</div>


<?= $this->element('Install.admin/footer'); ?>
<!-- jQuery -->
<?= $this->Html->script('Install.jquery.js'); ?>
<?= $this->Html->script('Install.bootstrap.min.js'); ?>
<!-- Bootstrap Core JavaScript -->
</body>

</html>
