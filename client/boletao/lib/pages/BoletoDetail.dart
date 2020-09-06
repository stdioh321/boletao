import 'dart:io';
import 'dart:typed_data';

import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:permission_handler/permission_handler.dart';

class BoletoDetail extends StatefulWidget {
  Uint8List imgBoleto;
  String boletoHtml;
  BoletoDetail({this.imgBoleto, this.boletoHtml});
  @override
  _BoletoDetailState createState() => _BoletoDetailState();
}

class _BoletoDetailState extends State<BoletoDetail> {
  @override
  initState() {
    super.initState();
    print(widget.boletoHtml);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text("Boleto"),
      ),
      body: SingleChildScrollView(
        child: Container(
          padding: EdgeInsets.all(15),
          alignment: Alignment.center,
          child: Image.memory(
            widget.imgBoleto,
            fit: BoxFit.contain,
          ),
        ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          if (Platform.isAndroid) {
            var file = File.fromRawPath(widget.imgBoleto);
            String fileName =
                "boleto_${DateTime.now().millisecondsSinceEpoch}.png";
            // print(fileName);
            if (await Permission.storage.isDenied == true) {
              var status = await Permission.storage.request();
            }

            if (await Permission.storage.isGranted == true) {
              // await file.
              var f = File("/sdcard/Download/${fileName}");
              Dio dio = Dio();
              f.writeAsBytesSync(file.readAsBytesSync());
              print("Saved ${fileName}");
            }
          }
          // if (kIsWeb == true) {
          //   var url = base64Encode(widget.imgBoleto.toList());
          //   print(url);
          //   html.AnchorElement anchorElement =
          //       new html.AnchorElement(href: "data:image/png;base64,${url}");
          //   anchorElement.download = "boletao.png";
          //   anchorElement.click();
          // }
        },
        child: Icon(Icons.save),
      ),
    );
  }
}
