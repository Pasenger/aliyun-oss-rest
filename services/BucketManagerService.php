<?php
/**
 * bucket manager
 * Created by PhpStorm.
 * User: pasenger
 * Date: 16/6/20
 * Time: 上午11:29
 */

require_once __DIR__ . '/CommonUtils.php';
require_once __DIR__ . '/Constants.php';

/**
 * 获取bucket列表
 */
$app->get('/api/bucket/get/all', function () use ($app)  {
    $result = array(
        "code" => 0,
        "message"   => "OK",
        "count" => 0,
        "dataList"  => array()
    );

    $callback = $app->request->get('callback');

    $bucketList = null;
    try{
        $bucketListInfo = $app->ossClient->listBuckets();
    } catch(Exception $e) {
        $result['code'] = 1;
        $result['message'] = "exception: ". $e->getMessage();
    }


    $bucketList = $bucketListInfo->getBucketList();
    foreach($bucketList as $bucket) {
        array_push($result['dataList'], array(
            "name"  => $bucket->getName(),
            "location"  => $bucket->getLocation(),
            "createdate"    => $bucket->getCreatedate()
        ));
    }

    $result['count'] = count($bucketList);


    echo CommonUtils::convertJsonpCallback($callback, $result);
});

/**
 * bucket是否存在
 * @param   string $bucket      bucket name
 * @param   string $callback    jsonp callback function name
 */
$app->get('/api/bucket/exist', function () use ($app)  {

    $bucket = $app->request->get('bucket');
    $callback = $app->request->get('callback');

    $result = array(
        "code" => 0,
        "message"   => "The bucket $bucket is existing.",
    );


    try {
        $res = $app->ossClient->doesBucketExist($bucket);
    } catch (Exception $e) {
        $result['code'] = 1;
        $result['message'] = "exception: ". $e->getMessage();
    }

    if ($res === false) {
        $result['code'] = 2;
        $result['message'] = "The bucket $bucket is not existing.";
    }

    echo CommonUtils::convertJsonpCallback($callback, $result);
});


/**
 * 创建Bucket
 * @param string    $bucket bucket name
 * @param int       $acl    acl type
 * @param string    $callback   jsonp callback function name
 */
$app->post('/api/bucket/create', function () use ($app)  {

    $bucket = $app->request->getPost('bucket');
    $aclType = $app->request->getPost('acl');
    $callback = $app->request->getPost('callback');

    $result = array(
        "code" => 0,
        "message"   => "OK.",
    );

    if($bucket == null || strlen($bucket) < 5 || strlen($bucket) > 63){
        $result['code'] = 1;
        $result['message'] = "The param bucket is correct: $bucket";

        die(CommonUtils::convertJsonpCallback($callback, $result));
    }

    if($aclType < 1 || $aclType > 3){
        $result['code'] = 2;
        $result['message'] = "The param acl is correct: $aclType";

        die(CommonUtils::convertJsonpCallback($callback, $result));
    }

    $acl = Constants::OSS_ACL_TYPE_PRIVATE;
    if($aclType == 2){
        $acl = Constants::OSS_ACL_TYPE_PUBLIC_READ;
    }else if($aclType == 3){
        $acl = Constants::OSS_ACL_TYPE_PUBLIC_READ_WRITE;
    }

    try {
        $app->ossClient->createBucket($bucket, $acl);
    } catch (Exception $e) {
        $result['code'] = 3;
        $result['message'] = "exception: ". $e->getMessage();
    }

    echo CommonUtils::convertJsonpCallback($callback, $result);
});

/**
 * 设置bucket的acl配置
 *
 * @param string $bucket 存储空间名称
 * @param int   $aclType    acl
 * @return null
 */
$app->put('/api/bucket/update/acl/{bucket}/{aclType}', function ($bucket, $aclType) use ($app)  {

    $result = array(
        "code" => 0,
        "message"   => "OK.",
    );

    if($bucket == null || strlen($bucket) < 5 || strlen($bucket) > 63){
        $result['code'] = 1;
        $result['message'] = "The param bucket is correct: $bucket";

        die(CommonUtils::convertJsonpCallback(null, $result));
    }

    if($aclType < 1 || $aclType > 3){
        $result['code'] = 2;
        $result['message'] = "The param acl is correct: $aclType";

        die(CommonUtils::convertJsonpCallback(null, $result));
    }

    $acl = Constants::OSS_ACL_TYPE_PRIVATE;
    if($aclType == 2){
        $acl = Constants::OSS_ACL_TYPE_PUBLIC_READ;
    }else if($aclType == 3){
        $acl = Constants::OSS_ACL_TYPE_PUBLIC_READ_WRITE;
    }

    try {
        $app->ossClient->putBucketAcl($bucket, $acl);
    } catch (Exception $e) {
        $result['code'] = 3;
        $result['message'] = "exception: ". $e->getMessage();
    }

    echo CommonUtils::convertJsonpCallback(null, $result);
});


/**
 * 获取bucket的acl配置
 *
 * @param string $bucket 存储空间名称
 * @return null
 */
$app->get('/api/bucket/get/acl/{bucket}', function ($bucket) use ($app)  {

    $result = array(
        "code" => 0,
        "message"   => "OK.",
    );

    if($bucket == null || strlen($bucket) < 5 || strlen($bucket) > 63){
        $result['code'] = 1;
        $result['message'] = "The param bucket is correct: $bucket";

        die(CommonUtils::convertJsonpCallback(null, $result));
    }

    try {
        $result['message'] = $app->ossClient->getBucketAcl($bucket);
    } catch (Exception $e) {
        $result['code'] = 3;
        $result['message'] = "exception: ". $e->getMessage();
    }

    echo CommonUtils::convertJsonpCallback(null, $result);
});

/**
 * 删除bucket
 * @param string $bucket bucket name
 */
$app->delete('/api/bucket/delete/{bucket}', function ($bucket) use ($app){
    $result = array(
        "code" => 0,
        "message"   => "OK.",
    );

    try{
        $app->ossClient->deleteBucket($bucket);
    } catch(Exception $e) {
        $result['code'] = 1;
        $result['message'] = "exception: ". $e->getMessage();
    }

    echo CommonUtils::convertJsonpCallback(null, $result);
});
