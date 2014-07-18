<?xml version="1.0" encoding="UTF-8"?>
<sld:StyledLayerDescriptor xmlns="http://www.opengis.net/sld" xmlns:sld="http://www.opengis.net/sld" xmlns:ogc="http://www.opengis.net/ogc" xmlns:gml="http://www.opengis.net/gml" version="1.0.0">
  <sld:NamedLayer>
    <sld:Name>Cell Tech</sld:Name>
    <sld:UserStyle>
      <sld:Name>cell_type</sld:Name>
      <sld:FeatureTypeStyle>
        <sld:Name>name</sld:Name>
        <sld:Rule>
          <sld:Title>CDMA Cell</sld:Title>
          <ogc:Filter>
		   <ogc:Or>
            <ogc:PropertyIsEqualTo>
              <ogc:PropertyName>sc_type</ogc:PropertyName>
              <ogc:Literal>CDMA</ogc:Literal>
            </ogc:PropertyIsEqualTo>
			<ogc:PropertyIsEqualTo>
              <ogc:PropertyName>sc_type</ogc:PropertyName>
              <ogc:Literal>TRAFFIC CARRIER</ogc:Literal>
            </ogc:PropertyIsEqualTo>
			</ogc:Or>
          </ogc:Filter>
		<sld:PointSymbolizer>
         <sld:Graphic>
           <sld:Mark>
             <sld:WellKnownName>circle</sld:WellKnownName>
             <sld:Fill>
               <sld:CssParameter name="fill">#33CC33</sld:CssParameter>
             </sld:Fill>
           </sld:Mark>
           <sld:Size>20</sld:Size>
         </sld:Graphic>
        </sld:PointSymbolizer>
        </sld:Rule>
		<sld:Rule>
          <sld:Title>EVDO Cell</sld:Title>
		  <ogc:Filter>
          <ogc:Or>
            <ogc:PropertyIsEqualTo>
              <ogc:PropertyName>sc_type</ogc:PropertyName>
              <ogc:Literal>EVDO</ogc:Literal>
            </ogc:PropertyIsEqualTo>
			<ogc:PropertyIsEqualTo>
              <ogc:PropertyName>sc_type</ogc:PropertyName>
              <ogc:Literal>TRAFFIC EV-DO CARRIER</ogc:Literal>
            </ogc:PropertyIsEqualTo>
			<ogc:PropertyIsEqualTo>
              <ogc:PropertyName>sc_type</ogc:PropertyName>
              <ogc:Literal>Traffic EV-DO Carrier</ogc:Literal>
            </ogc:PropertyIsEqualTo>
			</ogc:Or>
			</ogc:Filter>
          <sld:PointSymbolizer>
         <sld:Graphic>
           <sld:Mark>
             <sld:WellKnownName>circle</sld:WellKnownName>
             <sld:Fill>
               <sld:CssParameter name="fill">#C0C0C0</sld:CssParameter>
             </sld:Fill>
           </sld:Mark>
           <sld:Size>20</sld:Size>
         </sld:Graphic>
        </sld:PointSymbolizer>
        </sld:Rule>
      </sld:FeatureTypeStyle>
    </sld:UserStyle>
  </sld:NamedLayer>
</sld:StyledLayerDescriptor>

