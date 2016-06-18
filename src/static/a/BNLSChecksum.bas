Attribute VB_Name = "BNLSChecksumFunctions"
Option Explicit

Private Const CRC32_POLYNOMIAL As Long = &HEDB88320
Private CRC32Table(0 To 255) As Long

Private Sub InitCRC32()
    Dim I As Long, J As Long, K As Long, XorVal As Long
    
    Static CRC32Initialized As Boolean
    If CRC32Initialized Then Exit Sub
    CRC32Initialized = True
    
    For I = 0 To 255
        K = I
        
        For J = 1 To 8
            If K And 1 Then XorVal = CRC32_POLYNOMIAL Else XorVal = 0
            If K < 0 Then K = ((K And &H7FFFFFFF) \ 2) Or &H40000000 Else K = K \ 2
            K = K Xor XorVal
        Next
        
        CRC32Table(I) = K
    Next
End Sub

Private Function CRC32(ByVal Data As String) As Long
    Dim I As Long, J As Long
    
    Call InitCRC32
    
    CRC32 = &HFFFFFFFF
    
    For I = 1 To Len(Data)
        J = CByte(Asc(Mid(Data, I, 1))) Xor (CRC32 And &HFF&)
        If CRC32 < 0 Then CRC32 = ((CRC32 And &H7FFFFFFF) \ &H100&) Or &H800000 Else CRC32 = CRC32 \ &H100&
        CRC32 = CRC32 Xor CRC32Table(J)
    Next
    
    CRC32 = Not CRC32
End Function

Public Function BNLSChecksum(ByVal Password As String, ByVal ServerCode As Long) As Long
    BNLSChecksum = CRC32(Password & Right("0000000" & Hex(ServerCode), 8))
End Function
