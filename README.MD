云印新版服务接口
====================

## auth

登录验证
```
POST /auth/ 
{number,password,sch_id}
```

## user


#### /user/:id

获取用户信息
````
GET /user/123
````

修改信息(密码)
```
PUT /user/123
{md5($password),md5(old)}
```


## file

### /file/

获取文件列表
```
GET /file/
```

上传文件(客户端上传的方案)
```
POST /file/
{key}
``` 
### /file/token

获取上传token
返回key和token
```
POST /file/token
{name}
```

删除token（放弃上传）
```
delete /file/token/:key
```

### /file/id
获取详细信息
```
GET /file/123
```

修改文件名
```
PUT /file/123
{name}
```

删除文件
```
DELETE /file/123
```


## task

### /task/
打印任务列表
```
GET /task/
```

添加打印任务
```
POST /task/
{fid,pri_id,copies,color,isdouble,ppt,requrements}
```

### task/:id
修改打印任务
```
PUT /task/1234
{pri_id,copies,color,isdouble,ppt,requrements}
```

删除打印任务
```
DELETE /task/1234
```