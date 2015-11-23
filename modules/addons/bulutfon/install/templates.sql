INSERT INTO `mod_bulutfon_smstemplates`(`id`,`name`,`type`,`admingsm`,`template`,`variables`,`active`,`extra`,`description`) VALUES ( '', 'InvoiceCreated', 'client', '', 'Sayin {lastname}, {duedate} son odeme tarihli {total} TL tutarinda faturaniz olusturulmustur.', '{lastname},{duedate},{total}', '0', '', '{"turkish":"Yeni bir fatura olu\\u015fturuldu\\u011funda mesaj g\\u00f6nderir","english":"After invoice created"}' );
INSERT INTO `mod_bulutfon_smstemplates`(`id`,`name`,`type`,`admingsm`,`template`,`variables`,`active`,`extra`,`description`) VALUES ( '', 'AcceptOrder', 'client', '', 'Sayin {firstname} {lastname}, {orderid} numarali siparisiniz onaylanmistir. ', '{firstname},{lastname},{orderid}', '0', '', '{"turkish":"Sipari\\u015f onayland\\u0131\\u011f\\u0131nda mesaj g\\u00f6nderir","english":"After order accepted"}' );
INSERT INTO `mod_bulutfon_smstemplates`(`id`,`name`,`type`,`admingsm`,`template`,`variables`,`active`,`extra`,`description`) VALUES ( '', 'InvoicePaid', 'client', '', 'Sayin {firstname} {lastname}, {duedate} son odeme tarihli {total} TL tutarindaki faturaniz odenmistir. Odeme icin tesekkur ederiz.', '{firstname},{lastname},{duedate},{total}', '0', '', '{"turkish":"Faturan\\u0131z \\u00f6dendi\\u011finde mesaj g\\u00f6nderir","english":"Whenyou have paidthe billsends a message."}' );
