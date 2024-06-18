using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;

namespace Ispar
{
    public partial class Form4 : Form
    {
        public Form4()
        {
            InitializeComponent();
        }

        private void combospr_SelectedIndexChanged(object sender, EventArgs e)
        {
            if (combospr.SelectedIndex == -1)
            {
                pb.Image = null;
                pb.Visible = false;
            }
            if (combospr.SelectedIndex == 0)
            {
                pb.Image = Properties.Resources.k1;
                pb.Visible = true;
            }
            if (combospr.SelectedIndex == 1)
            {
                pb.Image = Properties.Resources.k2;
                pb.Visible = true;
            }
            if (combospr.SelectedIndex == 2)
            {
                pb.Image = Properties.Resources.tnk;
                pb.Visible = true;
            }
            if (combospr.SelectedIndex == 3)
            {
                pb.Image = Properties.Resources.kn;
                pb.Visible = true;
            }
            if (combospr.SelectedIndex == 4)
            {
                pb.Image = Properties.Resources.k0;
                pb.Visible = true;
            }
            if (combospr.SelectedIndex == 5)
            {
                pb.Image = Properties.Resources.pogr;
                pb.Visible = true;
            }

        }
    }
}
