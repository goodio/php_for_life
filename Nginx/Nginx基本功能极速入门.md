本文主要介绍一些Nginx的最基本功能以及简单配置，但不包括Nginx的安装部署以及实现原理。废话不多，直接开始。

## 静态HTTP服务器

首先，Nginx是一个HTTP服务器，可以将服务器上的静态文件（如HTML、图片）通过HTTP协议展现给客户端。

配置样例：

```
server {
    listen 80; # 端口号
    location / {
        root /usr/share/nginx/html; # 静态文件路径
    }
}
```

## 反向代理服务器

什么是反向代理？

客户端本来可以直接通过HTTP协议访问某网站应用服务器，如果网站管理员在中间加上一个Nginx，客户端请求Nginx，Nginx请求应用服务器，然后将结果返回给客户端，此时Nginx就是反向代理服务器。

![](./images/R-proxy_1.png)

配置：

```
server {
    listen 80;
    location / {
        proxy_pass http://192.168.20.1:8080; # 应用服务器HTTP地址
    }
}
```

## 负载均衡

当网站访问量非常大，网站站长开心赚钱的同时，也摊上事儿了。因为网站越来越慢，一台服务器已经不够用了。于是将相同的应用部署在多台服务器上，将大量用户的请求分配给多台机器处理。同时带来的好处是，其中一台服务器万一挂了，只要还有其他服务器正常运行，就不会影响用户使用。

Nginx可以通过反向代理来实现负载均衡。

![](./images/load-balancing_1.png)

配置：

```
upstream myapp {
    server 192.168.20.1:8080; # 应用服务器1
    server 192.168.20.2:8080; # 应用服务器2
}

server {
    listen 80;
    location / {
        proxy_pass http://myapp;
    }
}
```
以上配置会将请求轮询分配到应用服务器，也就是一个客户端的多次请求，有可能会由多台不同的服务器处理。可以通过ip-hash的方式，根据客户端ip地址的hash值将请求分配给固定的某一个服务器处理。或者weight来控制。

## 虚拟主机

有的网站访问量大，需要负载均衡。然而并不是所有网站都如此出色，有的网站，由于访问量太小，需要节省成本，将多个网站部署在同一台服务器上。

配置：

```
server {
    listen 80 default_server;
    server_name _;
    return 444; # 过滤其他域名的请求，返回444状态码
}

server {
    listen 80;
    server_name www.aaa.com; # www.aaa.com域名
    location / {
        proxy_pass http://localhost:8080; # 对应端口号8080
    }
}

server {
    listen80;
    server_name www.bbb.com; # www.bbb.com域名
    location / {
        proxy_pass http://localhost:8081; # 对应端口号8081
    }
}
```
在服务器8080和8081分别开了一个应用，客户端通过不同的域名访问，根据server_name可以反向代理到对应的应用服务器。
虚拟主机的原理是通过HTTP请求头中的Host是否匹配server_name来实现的，有兴趣的同学可以研究一下HTTP协议。
另外，server_name配置还可以过滤有人恶意将某些域名指向你的主机服务器。

## FastCGI

Nginx本身不支持PHP等语言，但是它可以通过FastCGI来将请求扔给某些语言或框架处理（例如PHP、Python、Perl）。

```
server {
    listen80;
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME /PHP文件路径 $fastcgi_script_name;  # PHP文件路径
        fastcgi_pass 127.0.0.1:9000; # PHP-FPM地址和端口号
        # 另一种方式：fastcgi_pass unix:/var/run/php5-fpm.sock;
    }
}

```

配置中将.php结尾的请求通过FashCGI交给PHP-FPM处理，PHP-FPM是PHP的一个FastCGI管理器。有关FashCGI可以查阅其他资料，本文不再介绍。

fastcgi_pass和proxy_pass有什么区别？下面一张图带你看明白：

![](./images/pass.jpg)