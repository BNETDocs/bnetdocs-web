#define CRC32_POLYNOMIAL 0xEDB88320
unsigned long CRC32Table[256];

void InitCRC32()
{
        static bool CRC32Initialized = false;
        if(CRC32Initialized)
                return;
        CRC32Initialized = true;

        for(unsigned long I = 0; I < 256; I++) {
                unsigned long K = I;
                for(unsigned long J = 0; J < 8; J++)
                        K = (K >> 1) ^ ((K & 1) ? CRC32_POLYNOMIAL : 0);
                CRC32Table[I] = K;
        }
}

unsigned long CRC32(unsigned char* Data, unsigned long Size)
{
        InitCRC32();

        unsigned long CRC = 0xffffffff;
        while(Size--)
                CRC = (CRC >> 8) ^ CRC32Table[(CRC & 0xff) ^ *Data++];
        return ~CRC;
}

inline unsigned char Hex(unsigned char Digit)
{
        if(Digit < 10)
                return Digit + '0';
        else
                return Digit - 10 + 'A';
}

unsigned long BNLSChecksum(const char* Password, unsigned long ServerCode)
{
        unsigned long Size = (unsigned long)strlen(Password);
        unsigned char* Data = new unsigned char[Size + 8];
        memcpy(Data, Password, Size);
        unsigned long I = 7;
        do {
                Data[Size + I] = Hex((unsigned char)ServerCode & 0xf);
                ServerCode >>= 4;
        }
        while(I--);

        unsigned long Checksum = CRC32(Data, Size + 8);
        delete[] Data;
        return Checksum;
}
