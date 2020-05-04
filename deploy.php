<?php
//REPONAME - имя репозитория
//USER - имя linux пользователя
//dev.site.com - папка с сайтом для разработки в нее копируется MASTER ветка
//config:
$repo_dir = '/var/www/USER/data/git/REPONAME.git';
$master_dir = '/var/www/USER/data/www/dev.site.com';
$production_dir = '/var/www/USER/data/www/site.com';

// Full path to git binary is required if git is not in your PHP user's path. Otherwise just use 'git'.
$git_bin_path = 'git';

//flag update
$update = false;

//try get json from bitbucket
$postData = file_get_contents('php://input');

if(empty($postData)){ 
	file_put_contents('deploy.log', date('m/d/Y h:i:s a') . " Empty $postData! \n", FILE_APPEND);
	exit(); 
}

$data = json_decode($postData, true);

if(!is_array($data)){ 
	file_put_contents('deploy.log', date('m/d/Y h:i:s a') . " The $data not array! \n", FILE_APPEND);
	exit();
}

// Examine the Bitbucket payload that’s being sent to deployment script
//file_put_contents('deploy.log', serialize($data) . "\n", FILE_APPEND);

$branch = $data['push']['changes'][0]['new']['name'];//production or master

//update only production or master
if(!$branch == 'production' &&  !$branch == 'master'){
	file_put_contents('deploy.log', date('m/d/Y h:i:s a') . " Unknown branch!  \n", FILE_APPEND);
	exit();
} else {
	$update = true;
}

if ($update) {
    // Do a git checkout to the web root
    exec('cd ' . $repo_dir . ' && ' . $git_bin_path  . ' fetch');
	if($branch == 'master'){
		exec('cd ' . $repo_dir . ' && GIT_WORK_TREE=' . $master_dir . ' ' . $git_bin_path  . ' checkout -f master');
	}
	if($branch == 'production'){
		exec('cd ' . $repo_dir . ' && GIT_WORK_TREE=' . $production_dir . ' ' . $git_bin_path  . ' checkout -f production');
	}

    // Log the deployment
    $commit_hash = shell_exec('cd ' . $repo_dir . ' && ' . $git_bin_path  . ' rev-parse --short HEAD');
    file_put_contents('deploy.log', date('m/d/Y h:i:s a') . " Deployed branch: " .  $branch . " Commit: " . $commit_hash . "\n", FILE_APPEND);
}
?>