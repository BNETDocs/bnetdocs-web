using System;

namespace Project1
{
        /// <summary>
        /// Summary description for BNLSChecksumCls 1.03.
        ///
        /// Copyright(c) 2004 - l)ragon
        ///
        /// My representation of the CRC32 in C#
        /// this is horible get a real language.
        ///
        /// 1.03 { Removed CRCTables and added a conversion of Yoni's VB InitCRC.,
        ///                Added HexValues table. }
        /// 1.02 { N/A }
        /// 1.01 { N/A }
        /// 1.00 { N/A }
        /// </summary>
        public class BNLSChecksumCls
        {
                public BNLSChecksumCls()
                {
                        //
                        // TODO: Add constructor logic here
                        //
                }
                static string[] HexValues = {   "00" ,"01" ,"02" ,"03" ,"04" ,"05" ,"06" ,"07" ,"08" ,"09" ,"0A" ,"0B" ,"0C" ,"0D" ,
                                                                                "0E" ,"0F" ,"10" ,"11" ,"12" ,"13" ,"14" ,"15" ,"16" ,"17" ,"18" ,"19" ,"1A" ,"1B" ,
                                                                                "1C" ,"1D" ,"1E" ,"1F" ,"20" ,"21" ,"22" ,"23" ,"24" ,"25" ,"26" ,"27" ,"28" ,"29" ,
                                                                                "2A" ,"2B" ,"2C" ,"2D" ,"2E" ,"2F" ,"30" ,"31" ,"32" ,"33" ,"34" ,"35" ,"36" ,"37" ,
                                                                                "38" ,"39" ,"3A" ,"3B" ,"3C" ,"3D" ,"3E" ,"3F" ,"40" ,"41" ,"42" ,"43" ,"44" ,"45" ,
                                                                                "46" ,"47" ,"48" ,"49" ,"4A" ,"4B" ,"4C" ,"4D" ,"4E" ,"4F" ,"50" ,"51" ,"52" ,"53" ,
                                                                                "54" ,"55" ,"56" ,"57" ,"58" ,"59" ,"5A" ,"5B" ,"5C" ,"5D" ,"5E" ,"5F" ,"60" ,"61" ,
                                                                                "62" ,"63" ,"64" ,"65" ,"66" ,"67" ,"68" ,"69" ,"6A" ,"6B" ,"6C" ,"6D" ,"6E" ,"6F" ,
                                                                                "70" ,"71" ,"72" ,"73" ,"74" ,"75" ,"76" ,"77" ,"78" ,"79" ,"7A" ,"7B" ,"7C" ,"7D" ,
                                                                                "7E" ,"7F" ,"80" ,"81" ,"82" ,"83" ,"84" ,"85" ,"86" ,"87" ,"88" ,"89" ,"8A" ,"8B" ,
                                                                                "8C" ,"8D" ,"8E" ,"8F" ,"90" ,"91" ,"92" ,"93" ,"94" ,"95" ,"96" ,"97" ,"98" ,"99" ,
                                                                                "9A" ,"9B" ,"9C" ,"9D" ,"9E" ,"9F" ,"A0" ,"A1" ,"A2" ,"A3" ,"A4" ,"A5" ,"A6" ,"A7" ,
                                                                                "A8" ,"A9" ,"AA" ,"AB" ,"AC" ,"AD" ,"AE" ,"AF" ,"B0" ,"B1" ,"B2" ,"B3" ,"B4" ,"B5" ,
                                                                                "B6" ,"B7" ,"B8" ,"B9" ,"BA" ,"BB" ,"BC" ,"BD" ,"BE" ,"BF" ,"C0" ,"C1" ,"C2" ,"C3" ,
                                                                                "C4" ,"C5" ,"C6" ,"C7" ,"C8" ,"C9" ,"CA" ,"CB" ,"CC" ,"CD" ,"CE" ,"CF" ,"D0" ,"D1" ,
                                                                                "D2" ,"D3" ,"D4" ,"D5" ,"D6" ,"D7" ,"D8" ,"D9" ,"DA" ,"DB" ,"DC" ,"DD" ,"DE" ,"DF" ,
                                                                                "E0" ,"E1" ,"E2" ,"E3" ,"E4" ,"E5" ,"E6" ,"E7" ,"E8" ,"E9" ,"EA" ,"EB" ,"EC" ,"ED" ,
                                                                                "EE" ,"EF" ,"F0" ,"F1" ,"F2" ,"F3" ,"F4" ,"F5" ,"F6" ,"F7" ,"F8" ,"F9" ,"FA" ,"FB" ,
                                                                                "FC" ,"FD" ,"FE" ,"FF" };

                public long CRC32_POLYNOMIAL = 0xEDB88320;
                public long[] CRC32Table;

                private void InitCRC32()
                {
                        long I, J, K, X, XorVal;

                        CRC32Table = new long[256];

                        for(I = 0; I < 256; I++) 
                        {
                                K = I;
        
                                for(J = 0; J < 8; J++)
                                {  
                                        X = (K & ((long)1));

                                        if(X > 0) 
                                        {       
                                                XorVal = CRC32_POLYNOMIAL; 
                                        } 
                                        else 
                                        {       
                                                XorVal = 0;     
                                        }
                                        if(K < 0) 
                                        {       
                                                K = ((K & 0x7FFFFFFF) / 2) | 0x40000000; 
                                        } 
                                        else    
                                        {
                                                K = K / 2; 
                                        }
                                        K ^= XorVal;
                                }
                                CRC32Table[I] = K;
                        }
                }
                private long CRC32(string txt) 
                {
                        long c=-1;
                        int I = 0, J = 0, L = 0, U = 0, U2 = 0;
                        InitCRC32();

                        for(I = 0; I < txt.Length; I++)
                        {
                                L = (int)txt[I];
                                U2 = 0xFF;
                                U = (int)(c & U2);
                                J = (L ^ U);
                                if(c < 0) 
                                { 
                                        c = (((c & 0x7FFFFFFF) / 0x100) | 0x800000); 
                                } 
                                else 
                                { 
                                        c = (c / 0x100); 
                                }
                                c ^= CRC32Table[J];
                                c = ConvertLong(c);
                        }
                        c = ((c * -1) - 1);
                        return c; 
                }
                private long ConvertLong(long Value)
                {
                        if(Value < 0xFFFFFFFF)
                        {
                                Value -= 0xFFFFFFFF;
                                Value -= 1;
                        }
                        if(Value > (0xFFFFFFFF * -1))
                        {
                                Value += 0xFFFFFFFF;
                                Value += 1;
                        }
                        return Value;
                }
                public string BNLSChecksum(string Password, long ServerCode)
                {
                        string strSCode = "", strTm = "";
                        char[] scChC;
                        
                        scChC = ServerCode.ToString().ToCharArray();
                        strSCode = MakeDW(ConvertLong(ServerCode));
                        strTm = reverse(strSCode);
                        strTm = tohex(strTm);

                        return MakeDW(CRC32(Password+strTm));
                }
                private string tohex(string inBuf)
                {
                        int x, numVal;
                        string outBuf="";

                        for(x = 0; x < inBuf.Length; x++)
                        {
                                numVal = inBuf[x];
                                outBuf = outBuf+HexValues[numVal];
                        }
                        return outBuf;
                }
                private string reverse(string inBuf)
                {
                        int x;
                        string outBuf = "";
                        for(x = inBuf.Length - 1; x > -1; x--)
                        {
                                outBuf = outBuf+inBuf[x];
                        }
                        return outBuf;
                }

                private string MakeDW(long lngInt)
                {
                        char a, b, c, d;
                        long tmpLng;
                        int tmpint;
                        string outBuf;

                        if(lngInt < -1) { lngInt += 0xFFFFFFFF; lngInt += 1; }
                                
                        tmpLng = lngInt;
                        
                        tmpint = (int)(((tmpLng / 256) / 256) / 256);
                        a = (char)(tmpint);
                        tmpLng -= (((tmpint* 256)* 256)* 256);
                        
                        tmpint = (int)((tmpLng / 256) / 256);
                        b = (char)(tmpint);
                        tmpLng -= ((tmpint*256)*256);
                        
                        tmpint = (int)(tmpLng / 256);
                        c = (char)(tmpint);
                        tmpLng -= (tmpint*256);
                        
                        tmpint = (int)(tmpLng);
                        d = (char)(tmpint);

                        outBuf = d.ToString()+c.ToString()+b.ToString()+a.ToString();
                        
                        return outBuf;
                }
        }
}
