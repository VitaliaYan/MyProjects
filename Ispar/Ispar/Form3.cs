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
    public partial class Form3 : Form
    {
        public string[] S = new string[2];
        Form1 newForm;
        public Form3(Form1 form)
        {
            InitializeComponent();
            this.newForm = form;
        }

        public void correct(string t1, string t2)
        {
            txtC.Text = t1;
            txtM.Text = t2;
        }
        public string[] correctS()
        {
            string[] S = new string[2];
            S[0] = txtC.Text;
            S[1] = txtM.Text;
            return S;
        }

        private void button1_Click(object sender, EventArgs e)
        {
            string[] S = new string[2];
            S = correctS();
            newForm.dgv2.Rows[newForm.arow].Cells[0].Value = S[0];
            newForm.dgv2.Rows[newForm.arow].Cells[1].Value = S[1];
            this.Close();
        }
    }
}
