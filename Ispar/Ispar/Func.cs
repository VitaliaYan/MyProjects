using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Ispar;
using System.Windows.Forms;

namespace Ispar
{
    public class Func
    {
        public RadioButton rbBlack;
        public RadioButton rbSouth;
        public RadioButton rbMiddle;
        public RadioButton rbNorth;
        public RadioButton rbAl;
        public double koeffcist()
        {
            double k4 = 0;
            if (rbBlack.Checked)
            {
                if (rbSouth.Checked) k4 = 1.29;
                if (rbMiddle.Checked) k4 = 1.18;
                if (rbNorth.Checked) k4 = 1.11;
            }
            if (rbAl.Checked)
            {
                if (rbSouth.Checked) k4 = 1.12;
                if (rbMiddle.Checked) k4 = 1.00;
                if (rbNorth.Checked) k4 = 0.96;
            }
            return k4;
        }

        public RadioButton rbmer;
        public ComboBox combo;
        public RadioButton rbbuff;

        public double koeff0()
        {
            double k0 = 0;
            if (rbmer.Checked)
            {
                if (combo.SelectedIndex == 0) k0 = 1.10;
                if (combo.SelectedIndex == 1) k0 = 1.00;
                if (combo.SelectedIndex == 2) k0 = 0.95;
                if (combo.SelectedIndex == 3) k0 = 0.20;
                if (combo.SelectedIndex == 4) k0 = 0.15;
                if (combo.SelectedIndex == 5) k0 = 0.20;
                if (combo.SelectedIndex == 6) k0 = 0.35;
                if (combo.SelectedIndex == 7) k0 = 0.45;
                if (combo.SelectedIndex == 8) k0 = 0.60;
                if (combo.SelectedIndex == 9) k0 = 0.70;
                if (combo.SelectedIndex == 10) k0 = 0.85;
            }
            if (rbbuff.Checked)
            {
                if (combo.SelectedIndex == 0) k0 = 0.30;
                if (combo.SelectedIndex == 1) k0 = 0.20;
                if (combo.SelectedIndex == 2) k0 = 0.19;
                if (combo.SelectedIndex == 3) k0 = 0.15;
                if (combo.SelectedIndex == 4) k0 = 0.10;
            }
            return k0;
        }

        
        public double koeffn(double n, double P38)
        {
            double kn = 0;
            if (n < 12)
            {
                if (P38 < 50)
                {
                    if (rbSouth.Checked) kn = 1.39;
                    if (rbMiddle.Checked) kn = 1.26;
                    if (rbNorth.Checked) kn = 1.20;
                }
                if (P38 >= 50 && P38 < 100)
                {
                    if (rbSouth.Checked) kn = 1.54;
                    if (rbMiddle.Checked) kn = 1.40;
                    if (rbNorth.Checked) kn = 1.31;
                }
                if (P38 >= 100 && P38 < 200)
                {
                    if (rbSouth.Checked) kn = 2.15;
                    if (rbMiddle.Checked) kn = 1.35;
                    if (rbNorth.Checked) kn = 1.79;
                }
                if (P38 >= 200 && P38 < 300)
                {
                    if (rbSouth.Checked) kn = 2.75;
                    if (rbMiddle.Checked) kn = 2.50;
                    if (rbNorth.Checked) kn = 2.27;
                }
                if (P38 >= 300 && P38 < 400)
                {
                    if (rbSouth.Checked) kn = 3.66;
                    if (rbMiddle.Checked) kn = 3.32;
                    if (rbNorth.Checked) kn = 3.02;
                }
                if (P38 >= 400)
                {
                    if (rbSouth.Checked) kn = 4.41;
                    if (rbMiddle.Checked) kn = 4.01;
                    if (rbNorth.Checked) kn = 3.65;
                }
            }
            if (n > 13 && n < 23)
            {
                if (P38 < 50)
                {
                    if (rbSouth.Checked) kn = 1.37;
                    if (rbMiddle.Checked) kn = 1.25;
                    if (rbNorth.Checked) kn = 1.19;
                }
                if (P38 >= 50 && P38 < 100)
                {
                    if (rbSouth.Checked) kn = 1.51;
                    if (rbMiddle.Checked) kn = 1.37;
                    if (rbNorth.Checked) kn = 1.29;
                }
                if (P38 >= 100 && P38 < 200)
                {
                    if (rbSouth.Checked) kn = 2.06;
                    if (rbMiddle.Checked) kn = 1.87;
                    if (rbNorth.Checked) kn = 1.73;
                }
                if (P38 >= 200 && P38 < 300)
                {
                    if (rbSouth.Checked) kn = 2.62;
                    if (rbMiddle.Checked) kn = 2.38;
                    if (rbNorth.Checked) kn = 2.16;
                }
                if (P38 >= 300 && P38 < 400)
                {
                    if (rbSouth.Checked) kn = 3.28;
                    if (rbMiddle.Checked) kn = 2.98;
                    if (rbNorth.Checked) kn = 2.71;
                }
                if (P38 >= 400)
                {
                    if (rbSouth.Checked) kn = 3.97;
                    if (rbMiddle.Checked) kn = 3.61;
                    if (rbNorth.Checked) kn = 3.28;
                }
            }
            if (n > 24 && n < 27)
            {
                if (P38 < 50)
                {
                    if (rbSouth.Checked) kn = 1.36;
                    if (rbMiddle.Checked) kn = 1.24;
                    if (rbNorth.Checked) kn = 1.18;
                }
                if (P38 >= 50 && P38 < 100)
                {
                    if (rbSouth.Checked) kn = 1.48;
                    if (rbMiddle.Checked) kn = 1.35;
                    if (rbNorth.Checked) kn = 1.27;
                }
                if (P38 >= 100 && P38 < 200)
                {
                    if (rbSouth.Checked) kn = 1.98;
                    if (rbMiddle.Checked) kn = 1.80;
                    if (rbNorth.Checked) kn = 1.67;
                }
                if (P38 >= 200 && P38 < 300)
                {
                    if (rbSouth.Checked) kn = 2.26;
                    if (rbMiddle.Checked) kn = 2.05;
                    if (rbNorth.Checked) kn = 2.00;
                }
                if (P38 >= 300 && P38 < 400)
                {
                    if (rbSouth.Checked) kn = 2.73;
                    if (rbMiddle.Checked) kn = 2.43;
                    if (rbNorth.Checked) kn = 2.40;
                }
                if (P38 >= 400)
                {
                    if (rbSouth.Checked) kn = 3.66;
                    if (rbMiddle.Checked) kn = 3.33;
                    if (rbNorth.Checked) kn = 3.03;
                }
            }
            if (n > 28 && n < 31)
            {
                if (P38 < 50)
                {
                    if (rbSouth.Checked) kn = 1.35;
                    if (rbMiddle.Checked) kn = 1.23;
                    if (rbNorth.Checked) kn = 1.17;
                }
                if (P38 >= 50 && P38 < 100)
                {
                    if (rbSouth.Checked) kn = 1.46;
                    if (rbMiddle.Checked) kn = 1.33;
                    if (rbNorth.Checked) kn = 1.25;
                }
                if (P38 >= 100 && P38 < 200)
                {
                    if (rbSouth.Checked) kn = 1.90;
                    if (rbMiddle.Checked) kn = 1.73;
                    if (rbNorth.Checked) kn = 1.59;
                }
                if (P38 >= 200 && P38 < 300)
                {
                    if (rbSouth.Checked) kn = 2.35;
                    if (rbMiddle.Checked) kn = 2.14;
                    if (rbNorth.Checked) kn = 1.94;
                }
                if (P38 >= 300 && P38 < 400)
                {
                    if (rbSouth.Checked) kn = 2.61;
                    if (rbMiddle.Checked) kn = 2.37;
                    if (rbNorth.Checked) kn = 2.15;
                }
                if (P38 >= 400)
                {
                    if (rbSouth.Checked) kn = 3.15;
                    if (rbMiddle.Checked) kn = 2.86;
                    if (rbNorth.Checked) kn = 2.86;
                }
            }
            if (n > 32 && n < 35)
            {
                if (P38 < 50)
                {
                    if (rbSouth.Checked) kn = 1.34;
                    if (rbMiddle.Checked) kn = 1.22;
                    if (rbNorth.Checked) kn = 1.16;
                }
                if (P38 >= 50 && P38 < 100)
                {
                    if (rbSouth.Checked) kn = 1.44;
                    if (rbMiddle.Checked) kn = 1.31;
                    if (rbNorth.Checked) kn = 1.23;
                }
                if (P38 >= 100 && P38 < 200)
                {
                    if (rbSouth.Checked) kn = 1.83;
                    if (rbMiddle.Checked) kn = 1.66;
                    if (rbNorth.Checked) kn = 1.53;
                }
                if (P38 >= 200 && P38 < 300)
                {
                    if (rbSouth.Checked) kn = 2.21;
                    if (rbMiddle.Checked) kn = 2.01;
                    if (rbNorth.Checked) kn = 1.83;
                }
                if (P38 >= 300 && P38 < 400)
                {
                    if (rbSouth.Checked) kn = 2.44;
                    if (rbMiddle.Checked) kn = 2.22;
                    if (rbNorth.Checked) kn = 2.02;
                }
                if (P38 >= 400)
                {
                    if (rbSouth.Checked) kn = 2.95;
                    if (rbMiddle.Checked) kn = 2.63;
                    if (rbNorth.Checked) kn = 2.44;
                }
            }
            if (n > 36 && n < 39)
            {
                if (P38 <= 50)
                {
                    if (rbSouth.Checked) kn = 1.33;
                    if (rbMiddle.Checked) kn = 1.21;
                    if (rbNorth.Checked) kn = 1.15;
                }
                if (P38 >= 50 && P38 < 100)
                {
                    if (rbSouth.Checked) kn = 1.42;
                    if (rbMiddle.Checked) kn = 1.29;
                    if (rbNorth.Checked) kn = 1.21;
                }
                if (P38 >= 100 && P38 < 200)
                {
                    if (rbSouth.Checked) kn = 1.75;
                    if (rbMiddle.Checked) kn = 1.59;
                    if (rbNorth.Checked) kn = 1.47;
                }
                if (P38 >= 200 && P38 < 300)
                {
                    if (rbSouth.Checked) kn = 2.09;
                    if (rbMiddle.Checked) kn = 1.90;
                    if (rbNorth.Checked) kn = 1.73;
                }
                if (P38 >= 300 && P38 < 400)
                {
                    if (rbSouth.Checked) kn = 2.33;
                    if (rbMiddle.Checked) kn = 2.12;
                    if (rbNorth.Checked) kn = 1.93;
                }
                if (P38 >= 400)
                {
                    if (rbSouth.Checked) kn = 2.83;
                    if (rbMiddle.Checked) kn = 2.57;
                    if (rbNorth.Checked) kn = 2.34;
                }
            }
            if (n > 40 && n < 43)
            {
                if (P38 < 50)
                {
                    if (rbSouth.Checked) kn = 1.32;
                    if (rbMiddle.Checked) kn = 1.20;
                    if (rbNorth.Checked) kn = 1.14;
                }
                if (P38 >= 50 && P38 < 100)
                {
                    if (rbSouth.Checked) kn = 1.40;
                    if (rbMiddle.Checked) kn = 1.27;
                    if (rbNorth.Checked) kn = 1.19;
                }
                if (P38 >= 100 && P38 < 200)
                {
                    if (rbSouth.Checked) kn = 1.66;
                    if (rbMiddle.Checked) kn = 1.51;
                    if (rbNorth.Checked) kn = 1.40;
                }
                if (P38 >= 200 && P38 < 300)
                {
                    if (rbSouth.Checked) kn = 1.91;
                    if (rbMiddle.Checked) kn = 1.74;
                    if (rbNorth.Checked) kn = 1.62;
                }
                if (P38 >= 300 && P38 < 400)
                {
                    if (rbSouth.Checked) kn = 2.11;
                    if (rbMiddle.Checked) kn = 1.92;
                    if (rbNorth.Checked) kn = 1.74;
                }
                if (P38 >= 400)
                {
                    if (rbSouth.Checked) kn = 2.55;
                    if (rbMiddle.Checked) kn = 2.33;
                    if (rbNorth.Checked) kn = 2.11;
                }
            }
            if (n > 44 && n < 47)
            {
                if (P38 < 50)
                {
                    if (rbSouth.Checked) kn = 1.31;
                    if (rbMiddle.Checked) kn = 1.19;
                    if (rbNorth.Checked) kn = 1.13;
                }
                if (P38 >= 50 && P38 < 100)
                {
                    if (rbSouth.Checked) kn = 1.38;
                    if (rbMiddle.Checked) kn = 1.25;
                    if (rbNorth.Checked) kn = 1.18;
                }
                if (P38 >= 100 && P38 < 200)
                {
                    if (rbSouth.Checked) kn = 1.60;
                    if (rbMiddle.Checked) kn = 1.45;
                    if (rbNorth.Checked) kn = 1.34;
                }
                if (P38 >= 200 && P38 < 300)
                {
                    if (rbSouth.Checked) kn = 1.80;
                    if (rbMiddle.Checked) kn = 1.64;
                    if (rbNorth.Checked) kn = 1.50;
                }
                if (P38 >= 300 && P38 < 400)
                {
                    if (rbSouth.Checked) kn = 1.99;
                    if (rbMiddle.Checked) kn = 1.81;
                    if (rbNorth.Checked) kn = 1.64;
                }
                if (P38 >= 400)
                {
                    if (rbSouth.Checked) kn = 2.41;
                    if (rbMiddle.Checked) kn = 2.19;
                    if (rbNorth.Checked) kn = 1.99;
                }
            }
            if (n > 48 && n < 51)
            {
                if (P38 < 50)
                {
                    if (rbSouth.Checked) kn = 1.30;
                    if (rbMiddle.Checked) kn = 1.18;
                    if (rbNorth.Checked) kn = 1.13;
                }
                if (P38 >= 50 && P38 < 100)
                {
                    if (rbSouth.Checked) kn = 1.35;
                    if (rbMiddle.Checked) kn = 1.23;
                    if (rbNorth.Checked) kn = 1.17;
                }
                if (P38 >= 100 && P38 < 200)
                {
                    if (rbSouth.Checked) kn = 1.54;
                    if (rbMiddle.Checked) kn = 1.40;
                    if (rbNorth.Checked) kn = 1.29;
                }
                if (P38 >= 200 && P38 < 300)
                {
                    if (rbSouth.Checked) kn = 1.72;
                    if (rbMiddle.Checked) kn = 1.56;
                    if (rbNorth.Checked) kn = 1.42;
                }
                if (P38 >= 300 && P38 < 400)
                {
                    if (rbSouth.Checked) kn = 1.89;
                    if (rbMiddle.Checked) kn = 1.72;
                    if (rbNorth.Checked) kn = 1.56;
                }
                if (P38 >= 400)
                {
                    if (rbSouth.Checked) kn = 2.29;
                    if (rbMiddle.Checked) kn = 2.08;
                    if (rbNorth.Checked) kn = 1.89;
                }
            }
            if (n > 52 && n < 55)
            {
                if (P38 < 50)
                {
                    if (rbSouth.Checked) kn = 1.29;
                    if (rbMiddle.Checked) kn = 1.17;
                    if (rbNorth.Checked) kn = 1.11;
                }
                if (P38 >= 50 && P38 < 100)
                {
                    if (rbSouth.Checked) kn = 1.34;
                    if (rbMiddle.Checked) kn = 1.22;
                    if (rbNorth.Checked) kn = 1.16;
                }
                if (P38 >= 100 && P38 < 200)
                {
                    if (rbSouth.Checked) kn = 1.48;
                    if (rbMiddle.Checked) kn = 1.36;
                    if (rbNorth.Checked) kn = 1.25;
                }
                if (P38 >= 200 && P38 < 300)
                {
                    if (rbSouth.Checked) kn = 1.62;
                    if (rbMiddle.Checked) kn = 1.47;
                    if (rbNorth.Checked) kn = 1.34;
                }
                if (P38 >= 300 && P38 < 400)
                {
                    if (rbSouth.Checked) kn = 1.76;
                    if (rbMiddle.Checked) kn = 1.60;
                    if (rbNorth.Checked) kn = 1.46;
                }
                if (P38 >= 400)
                {
                    if (rbSouth.Checked) kn = 2.13;
                    if (rbMiddle.Checked) kn = 1.94;
                    if (rbNorth.Checked) kn = 1.76;
                }
            }
            if (n > 56 && n < 59)
            {
                if (P38 < 50)
                {
                    if (rbSouth.Checked) kn = 1.28;
                    if (rbMiddle.Checked) kn = 1.16;
                    if (rbNorth.Checked) kn = 1.10;
                }
                if (P38 >= 50 && P38 < 100)
                {
                    if (rbSouth.Checked) kn = 1.32;
                    if (rbMiddle.Checked) kn = 1.20;
                    if (rbNorth.Checked) kn = 1.15;
                }
                if (P38 >= 100 && P38 < 200)
                {
                    if (rbSouth.Checked) kn = 1.44;
                    if (rbMiddle.Checked) kn = 1.31;
                    if (rbNorth.Checked) kn = 1.21;
                }
                if (P38 >= 200 && P38 < 300)
                {
                    if (rbSouth.Checked) kn = 1.56;
                    if (rbMiddle.Checked) kn = 1.41;
                    if (rbNorth.Checked) kn = 1.28;
                }
                if (P38 >= 300 && P38 < 400)
                {
                    if (rbSouth.Checked) kn = 1.69;
                    if (rbMiddle.Checked) kn = 1.54;
                    if (rbNorth.Checked) kn = 1.40;
                }
                if (P38 >= 400)
                {
                    if (rbSouth.Checked) kn = 2.06;
                    if (rbMiddle.Checked) kn = 1.86;
                    if (rbNorth.Checked) kn = 1.69;
                }
            }
            if (n > 60 && n < 63)
            {
                if (P38 < 50)
                {
                    if (rbSouth.Checked) kn = 1.27;
                    if (rbMiddle.Checked) kn = 1.15;
                    if (rbNorth.Checked) kn = 1.09;
                }
                if (P38 >= 50 && P38 < 100)
                {
                    if (rbSouth.Checked) kn = 1.30;
                    if (rbMiddle.Checked) kn = 1.18;
                    if (rbNorth.Checked) kn = 1.14;
                }
                if (P38 >= 100 && P38 < 200)
                {
                    if (rbSouth.Checked) kn = 1.40;
                    if (rbMiddle.Checked) kn = 1.27;
                    if (rbNorth.Checked) kn = 1.19;
                }
                if (P38 >= 200 && P38 < 300)
                {
                    if (rbSouth.Checked) kn = 1.51;
                    if (rbMiddle.Checked) kn = 1.37;
                    if (rbNorth.Checked) kn = 1.24;
                }
                if (P38 >= 300 && P38 < 400)
                {
                    if (rbSouth.Checked) kn = 1.63;
                    if (rbMiddle.Checked) kn = 1.48;
                    if (rbNorth.Checked) kn = 1.34;
                }
                if (P38 >= 400)
                {
                    if (rbSouth.Checked) kn = 1.97;
                    if (rbMiddle.Checked) kn = 1.79;
                    if (rbNorth.Checked) kn = 1.63;
                }
            }
            if (n > 64 && n < 67)
            {
                if (P38 < 50)
                {
                    if (rbSouth.Checked) kn = 1.26;
                    if (rbMiddle.Checked) kn = 1.14;
                    if (rbNorth.Checked) kn = 1.08;
                }
                if (P38 >= 50 && P38 < 100)
                {
                    if (rbSouth.Checked) kn = 1.29;
                    if (rbMiddle.Checked) kn = 1.17;
                    if (rbNorth.Checked) kn = 1.13;
                }
                if (P38 >= 100 && P38 < 200)
                {
                    if (rbSouth.Checked) kn = 1.38;
                    if (rbMiddle.Checked) kn = 1.35;
                    if (rbNorth.Checked) kn = 1.17;
                }
                if (P38 >= 200 && P38 < 300)
                {
                    if (rbSouth.Checked) kn = 1.47;
                    if (rbMiddle.Checked) kn = 1.34;
                    if (rbNorth.Checked) kn = 1.22;
                }
                if (P38 >= 300 && P38 < 400)
                {
                    if (rbSouth.Checked) kn = 1.57;
                    if (rbMiddle.Checked) kn = 1.43;
                    if (rbNorth.Checked) kn = 1.30;
                }
                if (P38 >= 400)
                {
                    if (rbSouth.Checked) kn = 1.90;
                    if (rbMiddle.Checked) kn = 1.73;
                    if (rbNorth.Checked) kn = 1.57;
                }
            }
            if (n > 68 && n < 71)
            {
                if (P38 < 50)
                {
                    if (rbSouth.Checked) kn = 1.24;
                    if (rbMiddle.Checked) kn = 1.13;
                    if (rbNorth.Checked) kn = 1.07;
                }
                if (P38 >= 50 && P38 < 100)
                {
                    if (rbSouth.Checked) kn = 1.28;
                    if (rbMiddle.Checked) kn = 1.16;
                    if (rbNorth.Checked) kn = 1.12;
                }
                if (P38 >= 100 && P38 < 200)
                {
                    if (rbSouth.Checked) kn = 1.35;
                    if (rbMiddle.Checked) kn = 1.23;
                    if (rbNorth.Checked) kn = 1.15;
                }
                if (P38 >= 200 && P38 < 300)
                {
                    if (rbSouth.Checked) kn = 1.44;
                    if (rbMiddle.Checked) kn = 1.31;
                    if (rbNorth.Checked) kn = 1.19;
                }
                if (P38 >= 300 && P38 < 400)
                {
                    if (rbSouth.Checked) kn = 1.53;
                    if (rbMiddle.Checked) kn = 1.39;
                    if (rbNorth.Checked) kn = 1.26;
                }
                if (P38 >= 400)
                {
                    if (rbSouth.Checked) kn = 1.84;
                    if (rbMiddle.Checked) kn = 1.68;
                    if (rbNorth.Checked) kn = 1.53;
                }
            }
            if (n > 72 && n < 75)
            {
                if (P38 < 50)
                {
                    if (rbSouth.Checked) kn = 1.23;
                    if (rbMiddle.Checked) kn = 1.12;
                    if (rbNorth.Checked) kn = 1.06;
                }
                if (P38 >= 50 && P38 < 100)
                {
                    if (rbSouth.Checked) kn = 1.26;
                    if (rbMiddle.Checked) kn = 1.15;
                    if (rbNorth.Checked) kn = 1.11;
                }
                if (P38 >= 100 && P38 < 200)
                {
                    if (rbSouth.Checked) kn = 1.33;
                    if (rbMiddle.Checked) kn = 1.21;
                    if (rbNorth.Checked) kn = 1.13;
                }
                if (P38 >= 200 && P38 < 300)
                {
                    if (rbSouth.Checked) kn = 1.40;
                    if (rbMiddle.Checked) kn = 1.27;
                    if (rbNorth.Checked) kn = 1.15;
                }
                if (P38 >= 300 && P38 < 400)
                {
                    if (rbSouth.Checked) kn = 1.49;
                    if (rbMiddle.Checked) kn = 1.36;
                    if (rbNorth.Checked) kn = 1.23;
                }
                if (P38 >= 400)
                {
                    if (rbSouth.Checked) kn = 1.80;
                    if (rbMiddle.Checked) kn = 1.64;
                    if (rbNorth.Checked) kn = 1.49;
                }
            }
            if (n > 76 && n < 79)
            {
                if (P38 < 50)
                {
                    if (rbSouth.Checked) kn = 1.22;
                    if (rbMiddle.Checked) kn = 1.11;
                    if (rbNorth.Checked) kn = 1.05;
                }
                if (P38 >= 50 && P38 < 100)
                {
                    if (rbSouth.Checked) kn = 1.28;
                    if (rbMiddle.Checked) kn = 1.14;
                    if (rbNorth.Checked) kn = 1.10;
                }
                if (P38 >= 100 && P38 < 200)
                {
                    if (rbSouth.Checked) kn = 1.31;
                    if (rbMiddle.Checked) kn = 1.19;
                    if (rbNorth.Checked) kn = 1.13;
                }
                if (P38 >= 200 && P38 < 300)
                {
                    if (rbSouth.Checked) kn = 1.37;
                    if (rbMiddle.Checked) kn = 1.26;
                    if (rbNorth.Checked) kn = 1.14;
                }
                if (P38 >= 300 && P38 < 400)
                {
                    if (rbSouth.Checked) kn = 1.46;
                    if (rbMiddle.Checked) kn = 1.32;
                    if (rbNorth.Checked) kn = 1.20;
                }
                if (P38 >= 400)
                {
                    if (rbSouth.Checked) kn = 1.76;
                    if (rbMiddle.Checked) kn = 1.60;
                    if (rbNorth.Checked) kn = 1.46;
                }
            }
            if (n > 80 && n < 105)
            {
                if (P38 < 50)
                {
                    if (rbSouth.Checked) kn = 1.21;
                    if (rbMiddle.Checked) kn = 1.10;
                    if (rbNorth.Checked) kn = 1.04;
                }
                if (P38 >= 50 && P38 < 100)
                {
                    if (rbSouth.Checked) kn = 1.24;
                    if (rbMiddle.Checked) kn = 1.13;
                    if (rbNorth.Checked) kn = 1.09;
                }
                if (P38 >= 100 && P38 < 200)
                {
                    if (rbSouth.Checked) kn = 1.30;
                    if (rbMiddle.Checked) kn = 1.18;
                    if (rbNorth.Checked) kn = 1.11;
                }
                if (P38 >= 200 && P38 < 300)
                {
                    if (rbSouth.Checked) kn = 1.35;
                    if (rbMiddle.Checked) kn = 1.23;
                    if (rbNorth.Checked) kn = 1.12;
                }
                if (P38 >= 300 && P38 < 400)
                {
                    if (rbSouth.Checked) kn = 1.43;
                    if (rbMiddle.Checked) kn = 1.30;
                    if (rbNorth.Checked) kn = 1.18;
                }
                if (P38 >= 400)
                {
                    if (rbSouth.Checked) kn = 1.73;
                    if (rbMiddle.Checked) kn = 1.67;
                    if (rbNorth.Checked) kn = 1.43;
                }
            }
            if (n > 106 && n < 131)
            {
                if (P38 < 50)
                {
                    if (rbSouth.Checked) kn = 1.19;
                    if (rbMiddle.Checked) kn = 1.09;
                    if (rbNorth.Checked) kn = 1.03;
                }
                if (P38 >= 50 && P38 < 100)
                {
                    if (rbSouth.Checked) kn = 1.23;
                    if (rbMiddle.Checked) kn = 1.12;
                    if (rbNorth.Checked) kn = 1.08;
                }
                if (P38 >= 100 && P38 < 200)
                {
                    if (rbSouth.Checked) kn = 1.28;
                    if (rbMiddle.Checked) kn = 1.16;
                    if (rbNorth.Checked) kn = 1.09;
                }
                if (P38 >= 200 && P38 < 300)
                {
                    if (rbSouth.Checked) kn = 1.33;
                    if (rbMiddle.Checked) kn = 1.21;
                    if (rbNorth.Checked) kn = 1.10;
                }
                if (P38 >= 300 && P38 < 400)
                {
                    if (rbSouth.Checked) kn = 1.41;
                    if (rbMiddle.Checked) kn = 1.28;
                    if (rbNorth.Checked) kn = 1.16;
                }
                if (P38 >= 400)
                {
                    if (rbSouth.Checked) kn = 1.71;
                    if (rbMiddle.Checked) kn = 1.55;
                    if (rbNorth.Checked) kn = 1.41;
                }
            }
            if (n > 132 && n < 200)
            {
                if (P38 < 50)
                {
                    if (rbSouth.Checked) kn = 1.17;
                    if (rbMiddle.Checked) kn = 1.08;
                    if (rbNorth.Checked) kn = 1.02;
                }
                if (P38 >= 50 && P38 < 100)
                {
                    if (rbSouth.Checked) kn = 1.22;
                    if (rbMiddle.Checked) kn = 1.11;
                    if (rbNorth.Checked) kn = 1.06;
                }
                if (P38 >= 100 && P38 < 200)
                {
                    if (rbSouth.Checked) kn = 1.27;
                    if (rbMiddle.Checked) kn = 1.15;
                    if (rbNorth.Checked) kn = 1.07;
                }
                if (P38 >= 200 && P38 < 300)
                {
                    if (rbSouth.Checked) kn = 1.31;
                    if (rbMiddle.Checked) kn = 1.19;
                    if (rbNorth.Checked) kn = 1.08;
                }
                if (P38 >= 300 && P38 < 400)
                {
                    if (rbSouth.Checked) kn = 1.38;
                    if (rbMiddle.Checked) kn = 1.26;
                    if (rbNorth.Checked) kn = 1.14;
                }
                if (P38 >= 400)
                {
                    if (rbSouth.Checked) kn = 1.68;
                    if (rbMiddle.Checked) kn = 1.53;
                    if (rbNorth.Checked) kn = 1.39;
                }
            }
            if (n > 200)
            {
                if (P38 < 50)
                {
                    if (rbSouth.Checked) kn = 1.17;
                    if (rbMiddle.Checked) kn = 1.07;
                    if (rbNorth.Checked) kn = 1.00;
                }
                if (P38 >= 50 && P38 < 100)
                {
                    if (rbSouth.Checked) kn = 1.20;
                    if (rbMiddle.Checked) kn = 1.10;
                    if (rbNorth.Checked) kn = 1.04;
                }
                if (P38 >= 100 && P38 < 200)
                {
                    if (rbSouth.Checked) kn = 1.24;
                    if (rbMiddle.Checked) kn = 1.13;
                    if (rbNorth.Checked) kn = 1.05;
                }
                if (P38 >= 200 && P38 < 300)
                {
                    if (rbSouth.Checked) kn = 1.28;
                    if (rbMiddle.Checked) kn = 1.17;
                    if (rbNorth.Checked) kn = 1.06;
                }
                if (P38 >= 300 && P38 < 400)
                {
                    if (rbSouth.Checked) kn = 1.31;
                    if (rbMiddle.Checked) kn = 1.20;
                    if (rbNorth.Checked) kn = 1.19;
                }
                if (P38 >= 400)
                {
                    if (rbSouth.Checked) kn = 1.59;
                    if (rbMiddle.Checked) kn = 1.45;
                    if (rbNorth.Checked) kn = 1.32;
                }
            }
            return kn;
        }

        public RadioButton rbTepl;
        public RadioButton rbNazem;
        public RadioButton rbHol;
        public RadioButton rbPodzem;
        public RadioButton rbEmal;
        public double[] koeff(double ta, double tg)
        {
            double k1 = 0, k2 = 0, k3 = 0, k4 = 0;
            double[] k = new double[4];
            if (rbTepl.Checked)
            {
                if (rbNazem.Checked)
                {
                    if (tg < 35)
                    {
                        k1 = 6.12;
                        k2 = 0.41;
                        k3 = 0.51;
                    }
                    if (35 < tg && tg < 50)
                    {
                        k1 = 4.33;
                        k2 = 0.37;
                        k3 = 0.59;
                    }
                    if (50 < tg && tg < 75)
                    {
                        k1 = -2.04;
                        k2 = 0.57;
                        k3 = 0.62;
                    }
                    if (tg > 75)
                    {
                        k1 = -8.41;
                        k2 = 0.99;
                        k3 = 0.75;
                    }
                    if (rbSouth.Checked)
                    {
                        if (rbBlack.Checked) k4 = 1.39;
                        if (rbAl.Checked) k4 = 1.14;
                        if (rbEmal.Checked) k4 = 0.92;
                    }
                    if (rbMiddle.Checked)
                    {
                        if (rbBlack.Checked) k4 = 1.22;
                        if (rbAl.Checked) k4 = 1.00;
                        if (rbEmal.Checked) k4 = 0.81;
                    }
                    if (rbNorth.Checked)
                    {
                        if (rbBlack.Checked) k4 = 1.12;
                        if (rbAl.Checked) k4 = 0.92;
                        if (rbEmal.Checked) k4 = 0.78;
                    }
                }
                if (rbPodzem.Checked)
                {
                    k4 = 1;
                    if (tg < 35)
                    {
                        k1 = 6.10;
                        k2 = 0.17;
                        k3 = 0.36;
                    }
                    if (35 < tg && tg < 50)
                    {
                        k1 = 0.30;
                        k2 = 0.15;
                        k3 = 0.75;
                    }
                    if (50 < tg && tg < 75)
                    {
                        k1 = 0.40;
                        k2 = 0.05;
                        k3 = 0.83;
                    }
                    if (tg > 75)
                    {
                        k1 = 8.95;
                        k2 = 0.07;
                        k3 = 0.65;
                    }
                }
            }
            if (rbHol.Checked)
            {
                if (rbNazem.Checked)
                {
                    if (tg < 20)
                    {
                        k1 = 0.30;
                        k2 = 0.37;
                        k3 = 0.62;
                    }
                    if (20 < tg && tg < 35)
                    {
                        k1 = 0;
                        k2 = 0.33;
                        k3 = 0.62;
                    }
                    if (35 < tg && tg < 60)
                    {
                        k1 = -5.77;
                        k2 = 0.26;
                        k3 = 0.77;
                    }
                    if (tg > 60)
                    {
                        k1 = -10.80;
                        k2 = 0.65;
                        k3 = 0.89;
                    }
                    if (rbSouth.Checked)
                    {
                        if (rbBlack.Checked) k4 = 1.39;
                        if (rbAl.Checked) k4 = 1.14;
                        if (rbEmal.Checked) k4 = 0.92;
                    }
                    if (rbMiddle.Checked)
                    {
                        if (rbBlack.Checked) k4 = 1.22;
                        if (rbAl.Checked) k4 = 1.00;
                        if (rbEmal.Checked) k4 = 0.81;
                    }
                    if (rbNorth.Checked)
                    {
                        if (rbBlack.Checked) k4 = 1.12;
                        if (rbAl.Checked) k4 = 0.92;
                        if (rbEmal.Checked) k4 = 0.78;
                    }
                }
                if (rbPodzem.Checked)
                {
                    k4 = 1;
                    if (tg < 25)
                    {
                        k1 = 1.62;
                        k2 = 0.19;
                        k3 = 0.74;
                    }
                    if (25 < tg && tg < 40)
                    {
                        k1 = 1.6;
                        k2 = 0.15;
                        k3 = 0.72;
                    }
                    if (40 < tg && tg < 60)
                    {
                        k1 = 1.6;
                        k2 = 0.10;
                        k3 = 0.70;
                    }
                    if (tg > 60)
                    {
                        k1 = 4.2;
                        k2 = 0.06;
                        k3 = 0.68;
                    }
                }
            }
            k[0] = k1;
            k[1] = k2;
            k[2] = k3;
            k[3] = k4;
            return k;
        }
    }
}
