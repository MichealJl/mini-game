
 
StorageManager 实例可以对文件（对象存储）进行管理。

获得当前环境下的 StorageManager 实例：

```php
$stroageManager = $tcbManager->getStorageManager();
```

- **key**：每个对象都有一个唯一的 key，对应于函数签名中的 key 参数，类似于文件路径，可通过分隔符 `/` 分隔 key，例如：`images/avatar/head.jpg`，`images/avatar/` 也是一个合法的 key，但是这个 key 对应的对象没有实际内容。注意：在对象存储中没有文件、文件夹等文件系统概念。
- **prefix**：从 ObjectKey 的第一个字符开始到任意字符，构成的字符串被称为 prefix，例如：`images/avatar/head.jpg` 的 prefix 有 `images/avatar/` 或 `images/ava` 等，甚至 `images/avatar/head.jpg` 也可以是一个合法的 prefix。

#### 目录

* [上传单个对象](#上传单个对象)
* [删除单个对象](#删除单个对象)
* [下载单个对象](#下载单个对象)
* [获取对象列表](#获取对象列表)
* [获取临时访问地址](#获取临时访问地址)
* [上传目录](#上传目录)
* [下载对象](#下载对象)
* [移除对象](#移除对象)
* [对象列表](#对象列表)

### 上传单个对象

#### 接口定义

该接口可上传单个文件到对象存储中。

```php
putObject(string $key, string $path, array $options = []): object
```

#### 参数说明

参数名              |  类型   | 描述
-------------------|---------|-------------------
$key               | String  | ObjectKey
$path              | String  | 文件路径，如果该路径是一个目录，则会在该目录下查找 $key 文件上传

**调用示例**

```php
$storageManager->putObject("/path/to/file", "path/to/asserts")
$storageManager->putObject("/image/head.ico", "/workspace/projcect")
```

**返回示例**

```
stdClass Object
(
    [RequestId] => 1563804348002_58102
)
```

**返回字段描述**

参数名           |  类型    | 描述
--------------- | ------ | -----------
RequestId       | String | 请求唯一标识

### 删除单个对象

#### 接口定义

该接口可删除一个对象存储中的对象。

```php
deleteObject(string $key): object
```

#### 参数说明

参数名              |  类型         | 描述
-------------------|---------------|-------------------
$key               | String        | ObjectKey

**调用示例**

```php
$storageManager->deleteObject($key)
```

**返回示例**（删除了公共响应字段）

```
stdClass Object
(
    [RequestId] => 1563804680285_12662
)
```

**返回字段描述**

参数名           |  类型   | 描述
--------------- | ------ | -----------
RequestId       | String | 请求唯一标识
Headers         | Array  | 无特定的头部字段
Body            | NULL   | 该接口无 Body

### 下载单个对象

#### 接口定义

该接口可下载一个对象到本地。

```php
getObject(string $key): object
```

#### 参数说明

参数名              |  类型          | 描述
-------------------|---------------|-------------------
$key               | String        | ObjectKey
target             | String        | 下载文件保存地址

```php
$storageManager->getObject($key, $target)
```

**返回值示例**

该接口无返回值。该接口会同时将对象写入 $target 指定路径。

### 获取对象列表

#### 接口定义

该接口可以获取对象存储中的对象列表。

```php
listObjects(array $options = []): object
```

#### 参数说明

参数名              |  类型          | 描述
-------------------|---------------|-------------------
$options           | Array         | 可选参数
 ⁃ prefix          | String        | 对象键匹配前缀，限定响应中只包含指定前缀的对象键，例如：src/，表示以 src 或 dist 为前缀的对象
 ⁃ delimiter       | Boolean       | 一个字符的分隔符，用于对 prefix 进行分组
 ⁃ max-keys        | Number        | 单次返回最大的条目数量，默认值为1000，最大为1000
 ⁃ marker          | Number        | ObjectKey，所有列出条目从 marker 开始，如果不能一次全部返回，则可通过此字段跳过

**调用示例**

```php
$storageManager->listObjects([
    "prefix" => "src/",
    "delimiter" => "",
    "max-keys" => 1000
]);
```

**返回示例**

```
stdClass Object
(
    [Name] => test-1251267563
    [Prefix] => src/
    [Marker] => 
    [MaxKeys] => 1000
    [Delimiter] => /
    [IsTruncated] => false
    [Contents] => Array
        (
            [0] => stdClass Object
                (
                    [Key] => src/
                    [LastModified] => 2019-06-12T07:08:33.000Z
                    [ETag] => "d41d8cd98f00b204e9800998ecf8427e"
                    [Size] => 0
                    [Owner] => stdClass Object
                        (
                            [ID] => 1251267563
                            [DisplayName] => 1251267563
                        )
                    [StorageClass] => STANDARD
                )
            [1] => stdClass Object
                (
                    [Key] => src/index.ts
                    [LastModified] => 2019-06-12T07:08:44.000Z
                    [ETag] => "4d212baa186498091dd7628d21540b1f"
                    [Size] => 25
                    [Owner] => stdClass Object
                        (
                            [ID] => 1251267563
                            [DisplayName] => 1251267563
                        )
                    [StorageClass] => STANDARD
                )
        )
)
```

**返回字段描述**

参数名                      |  类型    | 描述
---------------------------- | ------- | ---------------------------------------------
Name                         | String  | 说明 Bucket 的信息
Prefix                       | String  | 对象键匹配前缀，对应请求中的 prefix 参数
Marker                       | String  | 默认以 UTF-8 二进制顺序列出条目，所有列出条目从 marker 开始
NextMarker                   | String  | 假如返回条目被截断，则返回 NextMarker 就是下一个条目的起点
MaxKeys                      | String  | 单次响应请求内返回结果的最大的条目数量
Delimiter                    | String  | 分隔符，对应请求中的 delimiter 参数
IsTruncated                  | Boolean | 响应请求条目是否被截断，布尔值：true，false
Contents                     | Array   | 元数据信息
Contents[].Key               | String  | Object 的 Key
Contents[].LastModified      | String  | 说明 Object 最后被修改时间
Contents[].ETag              | String  | 文件的 MD5 算法校验值
Contents[].Size              | String  | 说明文件大小，单位是 Byte
Contents[].Owner             | String  | Bucket 持有者信息
Contents[].Owner.ID          | String  | Bucket 的 APPID
Contents[].Owner.DisplayName | String  | Object 持有者的名称
Contents[].StorageClass      | String  | Object 的存储类型，枚举值：STANDARD，STANDARD_IA，ARCHIVE。详情请参阅 存储类型 文档
CommonPrefixes               | Array   | 只有指定了 delimiter 参数的情况下才有可能包含该元素
CommonPrefixes[].Prefix      | String  | 单条 Common Prefix 的前缀

### 获取临时访问地址

#### 接口定义

该接口可获取对象的临时访问地址。

```php
getTemporaryObjectUrl(string $key, array $options): string
```

#### 参数说明

参数名                  |  类型    | 描述
-----------------------|---------------|-------------------
$key                   | String        | ObjectKey
$options               | Array         | 可选参数
 ⁃ expires             | String        | 有效期，默认为10分钟，请注意设置合理的有效期，格式为 [strtotime](https://php.net/manual/en/function.strtotime.php) 函数所接受的字符串

>!对象的访问权限需要对外开放，否则 URL 无法访问。

**调用示例**

```php
$url = $stroageManager->getTemporaryObjectUrl("functionName", [
    "expires" => "10 minutes"
]);
```

**返回示例**

```
https://6465-demo-619e0a-1251267563.tcb.qcloud.la/data/.gitkeep?sign=a8927e771da9b4afdf488922f1b8361b&t=1563778666
```

### 上传目录

#### 接口定义

该接口可以上传本地目录到对象存储中。

```php
upload(string $src, array $options = []): void
```

上传本地目录 $src 中的文件到对象存储桶的 $options["prefix"] 路径下。

#### 参数说明

参数名           |  类型          | 描述
----------------|---------------|-------------------
$src            | String        | 本地路径
$options        | Array         | 可选参数
 ⁃ prefix       | String        | 对象存储的指定 key 前缀，即路径，默认为根路径

**调用示例**

```php
$storageManager->upload($src, ["prefix" => "abc"])
```

该接口无返回值。

### 下载对象

#### 接口定义

该接口可下载对象存储中的具备相同 prefix 的对象到本地。

```php
download(string $dst, array $options = []): void
```

#### 参数说明

参数名         |  类型          | 描述
--------------|---------------|-------------------
$dst          | String        | 本地路径
$options      | Array         | 可选参数
 ⁃ prefix     | String        | 对象存储的指定 key 前缀，即路径，默认为根路径

**调用示例**

```php
$storageManager->upload($dst, ["prefix" => "src/"])
```

该接口无返回值。

### 移除对象

#### 接口定义

该接口可删除对象存储中的具备相同 `prefix` 的对象。

```php
remove(array $options = []): void
```

#### 参数说明

参数名           |  类型          | 描述
----------------|---------------|-------------------
$options        | Array         | 可选参数
 ⁃ prefix       | String        | 对象存储的指定 key 前缀，即路径，默认为根路径

**调用示例**

```php
$storageManager->remove(["prefix" => "src/"])
```

该接口无返回值。

### 对象列表

#### 接口定义

该接口可列出对象存储中的具备相同 prefix 的对象。

```php
keys(array $options = []): array
```

#### 参数说明

参数名         |  类型          | 描述
--------------|---------------|-------------------
$options      | Array         | 可选参数
 ⁃ prefix     | String        | 对象存储的指定 key 前缀，即路径，默认为根路径


**调用示例**

```php
$storageManager->keys(["prefix" => "src/"])
```

**返回示例**

```
Array
(
    [0] => upload/.gitignore
    [1] => upload/index.js
    [2] => upload/lib/index.js
    [3] => upload/文档.doc
)
```
