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
    public partial class Form2 : Form
    {
        Form1 newForm;
        public Form2(Form1 form)
        {
            InitializeComponent();
            this.newForm = form;
        }
        
        private void button1_Click(object sender, EventArgs e)
        {
            newForm.dgv2.Rows.Add(txtC.Text, txtM.Text);
            this.Close();
        }
    }
}
