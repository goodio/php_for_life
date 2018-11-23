## ICMP协议

<font color='red'>**ICMP**(Internet Control Message Protocol)是**介于网络层和传输层的协议**。它的主要功能是**传输网络诊断信息**。</font>

![](../images/icmp_1.jpg)

ICMP传输的信息可以分为两类，一类是<font color='red'>**错误(error)信息**</font>，这一类信息可用来诊断网络故障。我们已经知道，IP协议的工作方式是“Best Effort”，如果IP包没有被传送到目的地，或者IP包发生错误，IP协议本身不会做进一步的努力。但上游发送IP包的主机和接力的路由器并不知道下游发生了错误和故障，它们可能继续发送IP包。通过ICMP包，下游的路由器和主机可以将错误信息汇报给上游，从而让上游的路由器和主机进行调整。需要注意的是，ICMP只提供特定类型的错误汇报，它不能帮助IP协议成为“可靠”(reliable)的协议。另一类信息是<font color='red'>**咨询(Informational)**</font>性质的，比如某台计算机询问路径上的每个路由器都是谁，然后各个路由器同样用ICMP包回答。

(<font color='red'>**ICMP基于IP协议**。也就是说，**一个ICMP包需要封装在IP包中**，然后在互联网传送</font>。ICMP是IP套装的必须部分，也就是说，任何一个支持IP协议的计算机，都要同时实现ICMP。)

ICMP包的结构：

![A bunch of Types](../images/icmp_2.png)

<font color='red'>ICMP包都会有**Type**,** Code**和**Checksum**三部分</font>。Type表示ICMP包的大的类型，而Code是一个Type之内细分的小类型。针对不同的错误信息或者咨询信息，会有不同的Type和Code。从上面我们可以看到，ICMP支持的类型非常多，就好像瑞士军刀一样，有各种各样的功能。Checksum与IP协议的header checksum相类似，但与IP协议中checksum只校验头部不同，这里的Checksum所校验的是整个ICMP包(包括头部和数据)。

![](../images/icmp_3.jpg)

余下的ICMP包格式根据不同的类型不同。另一方面，ICMP包通常是由某个IP包触发的。这个触发IP包的头部和一部份数据会被包含在ICMP包的数据部分。

<font color='red'>ICMP协议是实现`ping`命令和`traceroute`命令的基础。这两个工具常用于网络排错</font>。

## 常见的ICMP包类型

### 回音

回音(Echo)属于咨询信息。`ping`命令就是利用了该类型的ICMP包。当使用`ping`命令的时候，将向目标主机发送Echo-询问类型的ICMP包，而目标主机在接收到该ICMP包之后，会回复Echo-回答类型的ICMP包，并将询问ICMP包包含在数据部分。`ping`命令是我们进行网络排查的一个重要工具。如果一个IP地址可以通过`ping`命令收到回复，那么其他的网络协议通信方式也很有可能成功。

### 源头冷却

源头冷却(source quench)属于错误信息。如果某个主机快速的向目的地传送数据，而目的地主机没有匹配的处理能力，目的地主机可以向出发主机发出该类型的ICMP包，提醒出发主机放慢发送速度(请温柔一点吧)

### 目的地无法到达

目的地无法到达(Destination Unreachable)属于错误信息。如果一个路由器接收到一个没办法进一步接力的IP包，它会向出发主机发送该类型的ICMP包。比如当IP包到达最后一个路由器，路由器发现目的地主机down机，就会向出发主机发送目的地无法到达(Destination Unreachable)类型的ICMP包。目的地无法到达还可能有其他的原因，比如不存在接力路径，比如不被接收的端口号等等。

### 超时

超时(Time Exceeded)属于错误信息。++IPv4中的Time to Live(TTL)++和++IPv6中的Hop Limit++会随着经过的路由器而递减，当这个区域值减为0时，就认为该IP包超时(Time Exceeded)。Time Exceeded就是TTL减为0时的路由器发给出发主机的ICMP包，通知它发生了超时错误。

`traceroute`就利用了这种类型的ICMP包。`traceroute`命令用来发现IP接力路径(route)上的各个路由器。它向目的地发送IP包，第一次的时候，将TTL设置为1，引发第一个路由器的Time Exceeded错误。这样，第一个路由器回复ICMP包，从而让出发主机知道途径的第一个路由器的信息。随后TTL被设置为2、3、4，...，直到到达目的主机。这样，沿途的每个路由器都会向出发主机发送ICMP包来汇报错误。`traceroute`将ICMP包的信息打印在屏幕上，就是接力路径的信息了。

### 重新定向

重新定向(redirect)属于错误信息。当一个路由器收到一个IP包，对照其routing table，发现自己不应该收到该IP包，它会向出发主机发送重新定向类型的ICMP，提醒出发主机修改自己的routing table。比如下面的网络：

![](../images/icmp_4.png)

假如145.1发送到145.15的IP包，结果被中间的路由器通过145.17的NIC收到。那么路由器会发现，根据自己的routing table，这个IP包要原路返回。那么router就可以判断出145.1的routing table可能有问题。所以路由器会向145.1发送redirect类型的ICMP包。

## IPv6的Neighbor Discovery

ARP协议用于发现周边的IP地址和MAC地址的对应。然而，ARP协议只用于IPv4，IPv6并不使用ARP协议。<font color=red>**IPv6**包通过邻居探索(**ND**, Neighbor Discovery)来实现ARP的功能。ND的工作方式与ARP类似，但它**基于ICMP协议**</font>。ICMP包有Neighbor Solicitation和Neighbor Advertisement类型。这两个类型分别对应ARP协议的询问和回复信息。

## 总结

ICMP协议是IP协议的排错帮手，它可以帮助人们及时发现IP通信中出现的故障。基于ICMP的ping和traceroute也构成了重要的网络诊断工具。然而，需要注意的是，尽管ICMP的设计是出于好的意图，但ICMP却经常被黑客借用进行网络攻击，比如利用伪造的IP包引发大量的ICMP回复，并将这些ICMP包导向受害主机，从而形成DoS攻击。而redirect类型的ICMP包可以引起某个主机更改自己的routing table，所以也被用作攻击工具。许多站点选择忽视某些类型的ICMP包来提高自身的安全性。



