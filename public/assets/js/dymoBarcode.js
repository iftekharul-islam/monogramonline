
function print_tray_label(batch, pieces, date) {

	try
            {
               
                var labelXml = '<?xml version="1.0" encoding="utf-8"?>\
				    <DieCutLabel Version="8.0" Units="twips">\
					<PaperOrientation>Landscape</PaperOrientation>\
					<Id>Small30336</Id>\
					<PaperName>30336 1 in x 2-1/8 in</PaperName>\
					<DrawCommands/>\
					<ObjectInfo>\
					    <TextObject>\
						<Name>Text</Name>\
						<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
						<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
						<LinkedObjectName></LinkedObjectName>\
						<Rotation>Rotation0</Rotation>\
						<IsMirrored>False</IsMirrored>\
						<IsVariable>True</IsVariable>\
						<HorizontalAlignment>Center</HorizontalAlignment>\
						<VerticalAlignment>Top</VerticalAlignment>\
						<TextFitMode>ShrinkToFit</TextFitMode>\
						<UseFullFontHeight>True</UseFullFontHeight>\
						<Verticalized>False</Verticalized>\
						<StyledText>\
								<Element>\
									<String xml:space="preserve">BATCHNUMBER</String>\
									<Attributes>\
										<Font Family="Arial" Size="11" Bold="True" Italic="False" Underline="False" Strikeout="False" />\
										<ForeColor Alpha="255" Red="0" Green="0" Blue="0" HueScale="100" />\
									</Attributes>\
								</Element>\
							</StyledText>\
					    </TextObject>\
					    <Bounds X="130" Y="150" Width="2696" Height="330" />\
					</ObjectInfo>\
					<ObjectInfo>\
					    <TextObject>\
						<Name>Text1</Name>\
						<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
						<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
						<LinkedObjectName></LinkedObjectName>\
						<Rotation>Rotation0</Rotation>\
						<IsMirrored>False</IsMirrored>\
						<IsVariable>True</IsVariable>\
						<HorizontalAlignment>Center</HorizontalAlignment>\
						<VerticalAlignment>Top</VerticalAlignment>\
						<TextFitMode>ShrinkToFit</TextFitMode>\
						<UseFullFontHeight>True</UseFullFontHeight>\
						<Verticalized>False</Verticalized>\
						<StyledText/>\
					    </TextObject>\
					    <Bounds X="130" Y="990" Width="2696" Height="330" />\
					</ObjectInfo>\
					<ObjectInfo>\
					    <TextObject>\
						<Name>Text2</Name>\
						<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
						<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
						<LinkedObjectName></LinkedObjectName>\
						<Rotation>Rotation0</Rotation>\
						<IsMirrored>False</IsMirrored>\
						<IsVariable>True</IsVariable>\
						<HorizontalAlignment>Center</HorizontalAlignment>\
						<VerticalAlignment>Top</VerticalAlignment>\
						<TextFitMode>ShrinkToFit</TextFitMode>\
						<UseFullFontHeight>True</UseFullFontHeight>\
						<Verticalized>False</Verticalized>\
						<StyledText/>\
					    </TextObject>\
					    <Bounds X="130" Y="548" Width="2696" Height="330" />\
					</ObjectInfo>\
				    </DieCutLabel>';

                var label = dymo.label.framework.openLabelXml(labelXml);

                // set label text
                label.setObjectText("Text", batch);
                label.setObjectText("Text1", pieces + ' PCS');
		label.setObjectText("Text2", date);

		var printerName = get_printer();

                label.print(printerName);
            }
            catch(e)
            {
                alert(e.message || e);
            }
}

function print_user_label(text, barcode) {
	
	try
            {
               
                var labelXml = '<?xml version="1.0" encoding="utf-8"?>\
				    <DieCutLabel Version="8.0" Units="twips">\
					<PaperOrientation>Landscape</PaperOrientation>\
					<Id>Small30336</Id>\
					<PaperName>30336 1 in x 2-1/8 in</PaperName>\
					<DrawCommands/>\
					<ObjectInfo>\
					    <TextObject>\
						<Name>Text</Name>\
						<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
						<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
						<LinkedObjectName></LinkedObjectName>\
						<Rotation>Rotation0</Rotation>\
						<IsMirrored>False</IsMirrored>\
						<IsVariable>True</IsVariable>\
						<HorizontalAlignment>Center</HorizontalAlignment>\
						<VerticalAlignment>Top</VerticalAlignment>\
						<TextFitMode>ShrinkToFit</TextFitMode>\
						<UseFullFontHeight>True</UseFullFontHeight>\
						<Verticalized>False</Verticalized>\
						<StyledText/>\
					    </TextObject>\
					    <Bounds X="130" Y="150" Width="2846" Height="210" />\
					</ObjectInfo>\
					<ObjectInfo>\
					    <BarcodeObject>\
						 <Name>Barcode</Name>\
						 <ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
						 <BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
						 <LinkedObjectName>BarcodeText</LinkedObjectName>\
						 <Rotation>Rotation0</Rotation>\
						 <IsMirrored>False</IsMirrored>\
						 <IsVariable>True</IsVariable>\
						 <Text>barCode</Text>\
						 <Type>Code128Auto</Type>\
						 <Size>Small</Size>\
						 <TextPosition>None</TextPosition>\
						 <TextFont Family="Arial" Size="8" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
						 <CheckSumFont Family="Arial" Size="8" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
						 <TextEmbedding>None</TextEmbedding>\
						 <ECLevel>0</ECLevel>\
						 <HorizontalAlignment>Center</HorizontalAlignment>\
						 <QuietZonesPadding Left="0" Top="0" Right="0" Bottom="0" />\
					     </BarcodeObject>\
					     <Bounds X="130" Y="510" Width="2846" Height="720" />\
					</ObjectInfo>\
				    </DieCutLabel>';

                var label = dymo.label.framework.openLabelXml(labelXml);

                // set label text
                label.setObjectText("Text", text);
                label.setObjectText('Barcode', barcode);
		
		var printerName = get_printer();

                label.print(printerName);
            }
            catch(e)
            {
                alert(e.message || e);
            }
	
}

function print_stock_label(stockno, bin, desc, url) {
	
	try
            {
               
                var labelXml = '<?xml version="1.0" encoding="utf-8"?>\
				    <DieCutLabel Version="8.0" Units="twips">\
					<PaperOrientation>Landscape</PaperOrientation>\
					<Id>Address</Id>\
					<PaperName>30252 Address</PaperName>\
					<DrawCommands/>\
					<ObjectInfo>\
						<TextObject>\
							<Name>Stock_label</Name>\
							<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
							<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
							<LinkedObjectName />\
							<Rotation>Rotation0</Rotation>\
							<IsMirrored>False</IsMirrored>\
							<IsVariable>False</IsVariable>\
							<GroupID>-1</GroupID>\
							<IsOutlined>False</IsOutlined>\
							<HorizontalAlignment>Left</HorizontalAlignment>\
							<VerticalAlignment>Top</VerticalAlignment>\
							<TextFitMode>ShrinkToFit</TextFitMode>\
							<UseFullFontHeight>True</UseFullFontHeight>\
							<Verticalized>False</Verticalized>\
							<StyledText>\
								<Element>\
									<String xml:space="preserve">Stock#</String>\
									<Attributes>\
										<Font Family="Arial" Size="11" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
										<ForeColor Alpha="255" Red="0" Green="0" Blue="0" HueScale="100" />\
									</Attributes>\
								</Element>\
							</StyledText>\
						</TextObject>\
						<Bounds X="331" Y="74" Width="764" Height="270" />\
					</ObjectInfo>\
					<ObjectInfo>\
						<TextObject>\
							<Name>Bin_label</Name>\
							<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
							<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
							<LinkedObjectName />\
							<Rotation>Rotation0</Rotation>\
							<IsMirrored>False</IsMirrored>\
							<IsVariable>False</IsVariable>\
							<GroupID>-1</GroupID>\
							<IsOutlined>False</IsOutlined>\
							<HorizontalAlignment>Left</HorizontalAlignment>\
							<VerticalAlignment>Top</VerticalAlignment>\
							<TextFitMode>ShrinkToFit</TextFitMode>\
							<UseFullFontHeight>True</UseFullFontHeight>\
							<Verticalized>False</Verticalized>\
							<StyledText>\
								<Element>\
									<String xml:space="preserve">Bin#</String>\
									<Attributes>\
										<Font Family="Arial" Size="11" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
										<ForeColor Alpha="255" Red="0" Green="0" Blue="0" HueScale="100" />\
									</Attributes>\
								</Element>\
							</StyledText>\
						</TextObject>\
						<Bounds X="1872" Y="72" Width="509" Height="270" />\
					</ObjectInfo>\
					<ObjectInfo>\
						<TextObject>\
							<Name>Stock_no</Name>\
							<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
							<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
							<LinkedObjectName />\
							<Rotation>Rotation0</Rotation>\
							<IsMirrored>False</IsMirrored>\
							<IsVariable>False</IsVariable>\
							<GroupID>-1</GroupID>\
							<IsOutlined>False</IsOutlined>\
							<HorizontalAlignment>Left</HorizontalAlignment>\
							<VerticalAlignment>Top</VerticalAlignment>\
							<TextFitMode>ShrinkToFit</TextFitMode>\
							<UseFullFontHeight>True</UseFullFontHeight>\
							<Verticalized>False</Verticalized>\
							<StyledText>\
								<Element>\
									<String xml:space="preserve">STOCKNO</String>\
									<Attributes>\
										<Font Family="Arial" Size="11" Bold="True" Italic="False" Underline="False" Strikeout="False" />\
										<ForeColor Alpha="255" Red="0" Green="0" Blue="0" HueScale="100" />\
									</Attributes>\
								</Element>\
							</StyledText>\
						</TextObject>\
						<Bounds X="1053" Y="72" Width="734" Height="270" />\
					</ObjectInfo>\
					<ObjectInfo>\
						<TextObject>\
							<Name>Bin</Name>\
							<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
							<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
							<LinkedObjectName />\
							<Rotation>Rotation0</Rotation>\
							<IsMirrored>False</IsMirrored>\
							<IsVariable>False</IsVariable>\
							<GroupID>-1</GroupID>\
							<IsOutlined>False</IsOutlined>\
							<HorizontalAlignment>Left</HorizontalAlignment>\
							<VerticalAlignment>Top</VerticalAlignment>\
							<TextFitMode>ShrinkToFit</TextFitMode>\
							<UseFullFontHeight>True</UseFullFontHeight>\
							<Verticalized>False</Verticalized>\
							<StyledText>\
								<Element>\
									<String xml:space="preserve">BIN</String>\
									<Attributes>\
										<Font Family="Arial" Size="11" Bold="True" Italic="False" Underline="False" Strikeout="False" />\
										<ForeColor Alpha="255" Red="0" Green="0" Blue="0" HueScale="100" />\
									</Attributes>\
								</Element>\
							</StyledText>\
						</TextObject>\
						<Bounds X="2386" Y="72" Width="1050" Height="270" />\
					</ObjectInfo>\
					<ObjectInfo>\
						<BarcodeObject>\
							<Name>Barcode</Name>\
							<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
							<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
							<LinkedObjectName />\
							<Rotation>Rotation0</Rotation>\
							<IsMirrored>False</IsMirrored>\
							<IsVariable>True</IsVariable>\
							<GroupID>-1</GroupID>\
							<IsOutlined>False</IsOutlined>\
							<Text>12345</Text>\
							<Type>Code39</Type>\
							<Size>Medium</Size>\
							<TextPosition>None</TextPosition>\
							<TextFont Family="Arial" Size="8" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
							<CheckSumFont Family="Arial" Size="8" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
							<TextEmbedding>None</TextEmbedding>\
							<ECLevel>0</ECLevel>\
							<HorizontalAlignment>Left</HorizontalAlignment>\
							<QuietZonesPadding Left="0" Top="0" Right="0" Bottom="0" />\
						</BarcodeObject>\
						<Bounds X="331" Y="435" Width="2910" Height="720" />\
					</ObjectInfo>\
					<ObjectInfo>\
						<ImageObject>\
							<Name>Stock_Image</Name>\
							<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
							<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
							<LinkedObjectName />\
							<Rotation>Rotation0</Rotation>\
							<IsMirrored>False</IsMirrored>\
							<IsVariable>False</IsVariable>\
							<GroupID>-1</GroupID>\
							<IsOutlined>False</IsOutlined>\
							<Image></Image>\
							<ScaleMode>Uniform</ScaleMode>\
							<BorderWidth>0</BorderWidth>\
							<BorderColor Alpha="255" Red="0" Green="0" Blue="0" />\
							<HorizontalAlignment>Center</HorizontalAlignment>\
							<VerticalAlignment>Center</VerticalAlignment>\
						</ImageObject>\
						<Bounds X="3378" Y="57" Width="1440" Height="1435" />\
					</ObjectInfo>\
					<ObjectInfo>\
						<TextObject>\
							<Name>Description</Name>\
							<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
							<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
							<LinkedObjectName />\
							<Rotation>Rotation0</Rotation>\
							<IsMirrored>False</IsMirrored>\
							<IsVariable>False</IsVariable>\
							<GroupID>-1</GroupID>\
							<IsOutlined>False</IsOutlined>\
							<HorizontalAlignment>Left</HorizontalAlignment>\
							<VerticalAlignment>Top</VerticalAlignment>\
							<TextFitMode>ShrinkToFit</TextFitMode>\
							<UseFullFontHeight>True</UseFullFontHeight>\
							<Verticalized>False</Verticalized>\
							<StyledText>\
								<Element>\
									<String xml:space="preserve">DESCRIPTION</String>\
									<Attributes>\
										<Font Family="Arial" Size="11" Bold="True" Italic="False" Underline="False" Strikeout="False" />\
										<ForeColor Alpha="255" Red="0" Green="0" Blue="0" HueScale="100" />\
									</Attributes>\
								</Element>\
							</StyledText>\
						</TextObject>\
						<Bounds X="331" Y="1208" Width="3617" Height="285" />\
					</ObjectInfo>\
				    </DieCutLabel>';

                var label = dymo.label.framework.openLabelXml(labelXml);

                // set label text
                label.setObjectText("Description", desc);
                label.setObjectText("Stock_no", stockno);
		label.setObjectText("Bin", bin);
		label.setObjectText("Barcode", stockno);
		
		// var myURI = dymo.label.framework.loadImageAsPngBase64(url);
		
		// label.setObjectText("Stock_Image",myURI);

		var printerName = get_printer();

                label.print(printerName);
            }
            catch(e)
            {
                alert(e.message || e);
            }
	
}

function get_printer() {

	// select printer to print on
        // for simplicity sake just use the first LabelWriter printer

        var printers = dymo.label.framework.getPrinters();

        if (printers.length == 0)
        	throw "No DYMO printers are installed. Install DYMO printers.";

        var printerName = "";
        
	for (var i = 0; i < printers.length; ++i)
        {
        	var printer = printers[i];
                
		if (printer.printerType == "LabelWriterPrinter")
             	{
                 	printerName = printer.name;
                        break;
                }
         }
                
         if (printerName == "")
         	throw "No LabelWriter printers found. Install LabelWriter printer";
	
	return printerName;
}
