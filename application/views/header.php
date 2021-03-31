<?php

$menuitems = [];

if (zl_is_logged_in()) {
    $menuitems[] = ['Dashboard', 'dashboard'];
    $menuitems[] = ['My Courses', 'course/list'];
    $menuitems[] = ['My Quizzes', 'quiz/list'];
    $menuitems[] = ['My Grades', 'quiz/grades'];
}

?>
<html>
    <head>
        <title><?php echo $title; ?> - <?php echo ZL_APP_TITLE; ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="<?php echo base_url('resources/bootstrap/css/bootstrap.min.css'); ?>">
        <link rel="stylesheet" href="<?php echo base_url('resources/fontawesome/css/all.min.css'); ?>">
        <script src="<?php echo base_url('resources/jquery/js/jquery-3.6.0.min.js'); ?>"></script>
        <script src="<?php echo base_url('resources/bootstrap/js/bootstrap.min.js'); ?>"></script>
        <script src="<?php echo base_url('resources/marked/js/marked.min.js'); ?>"></script>
    </head>
    <body>
        <header>
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-lg">
                    <a class="navbar-brand" href="<?php echo base_url(); ?>"><?php echo ZL_APP_TITLE; ?></a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbar-menu">
                        <ul class="navbar-nav">
                            <?php foreach ($menuitems as $item): ?>
                                <li class="nav-item"><a class="nav-link <?php echo (uri_string() === $item[1]) ? 'active' : ''; ?>" href="<?php echo site_url($item[1]); ?>"><?php echo $item[0]; ?></a></li>
                            <?php endforeach; ?>
                            <?php if (zl_is_logged_in()): ?>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Profile</a>
                                    <ul class="dropdown-menu">
                                        <li><h6 class="dropdown-header" style="width: 300px"><?php echo zl_session_get('name'); ?> <?php if (!empty(zl_session_get('sso'))): ?><span class="badge bg-secondary">SSO</span><?php endif; ?></h6></li>
                                        <li><a class="dropdown-item" href="<?php echo site_url('settings'); ?>"><span class="fa fa-cog me-1"></span> Settings</a></li>
                                        <li><a class="dropdown-item text-danger" onclick="return confirm('Are you sure?')" href="<?php echo site_url('authentication/logout'); ?>"><span class="fa fa-sign-out-alt me-1"></span> Logout</a></li>
                                    </ul>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>
        <div style="display: flex; flex-direction: column; min-height: 100vh">
            <main class="container-lg py-3" style="flex: 1 0 auto">

