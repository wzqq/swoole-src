--TEST--
swoole_http_client_coro: upload file
--SKIPIF--
<?php require  __DIR__ . '/../include/skipif.inc'; ?>
--FILE--
<?php
require_once __DIR__ . '/../include/bootstrap.php';

$pm = new ProcessManager;
$pm->parentFunc = function ($pid)
{
    go(function() {
        $cli = new Swoole\Coroutine\Http\Client('127.0.0.1', 9501);
        $cli->addFile(TEST_IMAGE, 'test.jpg');
        $cli->post('/upload_file', array('name' => 'rango'));
        assert($cli->statusCode == 200);
        $ret = json_decode($cli->body, true);
        assert($ret and is_array($ret));
        assert(md5_file(TEST_IMAGE) == $ret['md5']);
        $cli->close();
    });
    swoole_event::wait();
    swoole_process::kill($pid);
};

$pm->childFunc = function () use ($pm)
{
    include __DIR__ . "/../include/api/http_server.php";
};

$pm->childFirst();
$pm->run();
?>
--EXPECT--

