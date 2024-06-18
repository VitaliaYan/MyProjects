using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;
using Ispar;


namespace Ispar
{
    public partial class Form1 : Form
    {
        double ta, tg, tr;
        double[] K = new double[4];
        double Cv;
        double P38, Ptr, Pa, ptr;
        double kn, k0, Vg;
        public int arow;


        public Form1()
        {
            InitializeComponent();
        }

        private void btntr_Click(object sender, EventArgs e)
        {
            Func func = new Func();
            func.rbBlack = this.cbBlack;
            func.rbSouth = this.cbSouth;
            func.rbMiddle = this.cbMiddle;
            func.rbNorth = this.cbNorth;
            func.rbAl = this.cbAl;
            func.rbEmal = this.cbEmal;
            func.rbPodzem = this.cbPodzem;
            func.rbNazem = this.cbNazem;
            func.rbHol = this.cbHol;
            func.rbTepl = this.cbTepl;
            try
            {
                ta = Convert.ToSingle(txtta.Text);
                tg = Convert.ToSingle(txttg.Text);
                if (cbres.Checked)
                {
                    K = func.koeff(ta, tg);
                    tr = K[3] * (K[0] + K[1] * ta + K[2] * tg);
                }
                if (cbcist.Checked)
                {
                    if (cbTepl.Checked) tr = 0.5 * func.koeffcist() * (ta + tg);
                    if (cbHol.Checked) tr = 0.5 * (ta + tg);
                }
                lblPtr.Text = "Давление при Tr = " + tr.ToString() + ", мм.рт.ст.";
                lblrotr.Text = "Давление при Tr" + "(" + tr.ToString() + ") (Ptr), мм.рт.ст.";
                lblcv.Text = "Весовая концентрация насыщенных паров (Cv) при Tr(" + tr.ToString() + ")";
                lbltr.Text = "Температурный режим Tr = " + tr.ToString();
                Reservuar.TabPages[0].Enabled = false;
                Reservuar.TabPages[1].Enabled = true;
                Reservuar.TabPages[2].Enabled = false;
                Reservuar.TabPages[3].Enabled = false;
                Reservuar.TabPages[4].Enabled = false;
                string[] row = new string[] { "Tr (C)", tr.ToString() };
                dgv1.Rows.Add(row);
            }
            catch
            {
                MessageBox.Show("Заполните все значения", "Ошибка", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
        }

        private void btnP_Click(object sender, EventArgs e)
        {
            try
            {
                if (cbHigh.Checked)
                {
                    P38 = Convert.ToSingle(txtP38.Text);
                    Cv = Convert.ToSingle(txtcv.Text);
                    Reservuar.TabPages[0].Enabled = false;
                    Reservuar.TabPages[1].Enabled = false;
                    Reservuar.TabPages[2].Enabled = true;
                    Reservuar.TabPages[3].Enabled = false;
                    Reservuar.TabPages[4].Enabled = false;
                    dgv2.Enabled = false;
                    btnadd.Enabled = false;
                    btndel.Enabled = false;
                    btncor.Enabled = false;
                }
                else
                {
                    P38 = Convert.ToSingle(txtP38.Text);
                    Ptr = Convert.ToSingle(txtPtr.Text);
                    Reservuar.TabPages[0].Enabled = false;
                    Reservuar.TabPages[1].Enabled = false;
                    Reservuar.TabPages[2].Enabled = true;
                    Reservuar.TabPages[3].Enabled = false;
                    Reservuar.TabPages[4].Enabled = false;
                    dgv2.Enabled = true;
                    btnadd.Enabled = true;
                    btndel.Enabled = true;
                    btncor.Enabled = true;
                }
            }
            catch
            {
                MessageBox.Show("Заполните все значения", "Ошибка", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }

        }

        private void btnro_Click(object sender, EventArgs e)
        {
            try
            {
                if (cbHigh.Checked)
                {
                    Pa = Convert.ToSingle(txtPa.Text);
                }
                else
                {
                    double M, sum = 0;
                    int n = dgv2.RowCount - 1;
                    double[] C = new double[n];
                    double[] Mi = new double[n];
                    for (int i = 0; i < n; i++)
                    {
                        C[i] = Convert.ToSingle(dgv2.Rows[i].Cells[0].Value);
                        Mi[i] = Convert.ToSingle(dgv2.Rows[i].Cells[1].Value);
                    }
                    for (int i = 0; i < n; i++)
                    {
                        sum += C[i] / Mi[i];
                    }
                    Pa = Convert.ToSingle(txtPa.Text);
                    M = 100 / sum;
                    double p0 = M / 22.4;
                    ptr = p0 * (273 / (273 + tr)) * (Pa / 760);
                    lblronu.Text = "Плотность при НУ = " + p0.ToString();
                    lblrotr.Text = "Давление при Tr" + "(" + tr.ToString() + ") = " + ptr.ToString();
                    lblronu.Visible = true;
                    lblrotr.Visible = true;
                    string[] row = new string[] { "p0 (кг/м3)", p0.ToString() };
                    dgv1.Rows.Add(row);
                    row = new string[] { "ptr (кг/м3)", ptr.ToString() };
                    dgv1.Rows.Add(row);
                }
                Reservuar.TabPages[0].Enabled = false;
                Reservuar.TabPages[1].Enabled = false;
                Reservuar.TabPages[2].Enabled = false;
                Reservuar.TabPages[3].Enabled = true;
                Reservuar.TabPages[4].Enabled = false;
            }
            catch
            {
                MessageBox.Show("Заполните все значения", "Ошибка", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
        }

        private void btnadd_Click(object sender, EventArgs e)
        {
            Form2 newForm = new Form2(this);
            newForm.Show();
        }

        private void btndel_Click(object sender, EventArgs e)
        {
            int a = dgv2.CurrentRow.Index;
            dgv2.Rows.Remove(dgv2.Rows[a]);
        }

        private void btncor_Click(object sender, EventArgs e)
        {
            Form3 newForm = new Form3(this);
            newForm.Show();
            arow = dgv2.CurrentRow.Index;
            newForm.correct(dgv2.Rows[arow].Cells[0].Value.ToString(), dgv2.Rows[arow].Cells[1].Value.ToString());
        }

        private void btnK_Click(object sender, EventArgs e)
        {
            Func func = new Func();
            func.rbbuff = this.cbbuff;
            func.combo = this.comboosn;
            func.rbmer = this.cbmer;
            func.rbSouth = this.cbSouth;
            func.rbMiddle = this.cbMiddle;
            func.rbNorth = this.cbNorth;
            try
            {
                Vg = Convert.ToSingle(txtVg.Text);
                double Vp = Convert.ToSingle(txtVp.Text);
                if (cbres.Checked)
                {
                    double n = 2 * Vg / Vp;
                    kn = func.koeffn(n, P38);
                    k0 = func.koeff0();
                    lbln.Text = "Условная оборачиваемость, n/год = " + n.ToString();
                    lblkn.Text = "Коэффициент оборачиваемости Kn = " + kn.ToString();
                    lblk0.Text = "Коэффициент оснащенности K0 = " + k0.ToString();
                    lbln.Visible = true;
                    lblkn.Visible = true;
                    lblk0.Visible = true;
                    string[] row = new string[] { "n (раз/год)", n.ToString() };
                    dgv1.Rows.Add(row);
                    row = new string[] { "K0", k0.ToString() };
                    dgv1.Rows.Add(row);
                    row = new string[] { "Kn", kn.ToString() };
                    dgv1.Rows.Add(row);
                }
                Reservuar.TabPages[0].Enabled = false;
                Reservuar.TabPages[1].Enabled = false;
                Reservuar.TabPages[2].Enabled = false;
                Reservuar.TabPages[3].Enabled = false;
                Reservuar.TabPages[4].Enabled = true;

            }
            catch
            {
                MessageBox.Show("Заполните все значения", "Ошибка", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
        }

        private void btnout_Click(object sender, EventArgs e)
        {
            try
            {
                double Gp = 0, qp, Gpc;
                if (cbres.Checked)
                {
                    if (cbLow.Checked)
                    {
                        Gp = Vg * Ptr / Pa * ptr * kn * k0 * Math.Pow(10, -3);
                    }
                    if (cbHigh.Checked)
                    {
                        txtcv.Visible = true;
                        lblcv.Visible = true;
                        Gp = Vg * Cv * kn * k0 * Math.Pow(10, -6);
                    }
                }
                if (cbcist.Checked)
                {
                    if (cbLow.Checked)
                    {
                        Gp = Vg * Ptr / Pa * ptr * Math.Pow(10, -3);
                    }
                    if (cbHigh.Checked)
                    {
                        txtcv.Visible = true;
                        lblcv.Visible = true;
                        Gp = Vg * Cv * Math.Pow(10, -6);
                    }
                }
                qp = Gp * Math.Pow(10, 3) / (Vg * Convert.ToSingle(txtdt.Text));
                Gpc = Gp * Math.Pow(10, 6) / (3600 * Convert.ToSingle(txttau.Text));
                lblGc.Text = "Потери (т/период) = " + Gp.ToString();
                lblqp.Text = "Удельные потери (кг/тонну) = " + qp.ToString();
                lblGpc.Text = "Потери в единицу времени(г / с) = " + Gpc.ToString();
                lblGc.Visible = true;
                lblqp.Visible = true;
                lblGpc.Visible = true;
                string[] row = new string[] { "Gp (т/период)", Gp.ToString() };
                dgv1.Rows.Add(row);
                row = new string[] { "qp (кг/т)", qp.ToString() };
                dgv1.Rows.Add(row);
                row = new string[] { "Gpc (г/с)", Gpc.ToString() };
                dgv1.Rows.Add(row);
            }
            catch
            {
                MessageBox.Show("Заполните все значения", "Ошибка", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
        }

        private void cbPodzem_CheckedChanged(object sender, EventArgs e)
        {
            if (cbPodzem.Checked == true)
            {
                panel4.Enabled = false;
            }
            else
            {
                panel4.Enabled = true;
            }
        }

        private void cbSouth_MouseEnter(object sender, EventArgs e)
        {
            toolTip1.SetToolTip(cbSouth, "Государства: Азербайджан, Армения, Грузия, Кыргызстан, Молдаван, Таджикистан, Туркменистан, Узбекистан." + "\r\n" +
                "Республики: Дагестанская, Кабардино - Балкарская, Калмыцкая, Северо -Осетинская, Чечено - Ингушская. Края: Краснодарской, Ставропольский. " + "\r\n" +
                "Область Российской Федерации - Астраханская, Белгородская, Ростовская; Украины - Херсонская, Запорожская, Николаевская, Крымская, Одесская; " + "\r\n" +
                "Казахстана - Гурьевская, Джамбульская, Кзыл - Ординская, Чингитская.");
        }

        private void cbNorth_MouseEnter(object sender, EventArgs e)
        {
            toolTip1.SetToolTip(cbNorth, "Республики: Бурятская, Карельская, Коми, Тувинская, Якутская." + "\r\n" +
               "Области: Амурская, Архангельская, Мурманская, Новосибирская, Омская, Пермская, Свердловская, Тюменская, Томская, Читинская. " + "\r\n" +
               "Казахстана - Гурьевская, Джамбульская, Кзыл - Ординская, Чингитская.");
        }

        private void Form1_Load(object sender, EventArgs e)
        {
            Reservuar.TabPages[0].Enabled = true;
            Reservuar.TabPages[1].Enabled = false;
            Reservuar.TabPages[2].Enabled = false;
            Reservuar.TabPages[3].Enabled = false;
            Reservuar.TabPages[4].Enabled = false;
        }

        private void ClearC()
        {
            foreach (TabPage page in Reservuar.Controls)
            {
                foreach (Control c in page.Controls)
                {
                    if (c is TextBox)
                        c.Text = "";
                    if (c is Panel)
                    {
                        foreach (RadioButton rb in c.Controls)
                            rb.Checked = false;
                    }
                }
            }
            dgv1.Rows.Clear();
            dgv2.Rows.Clear();
            comboosn.Text = "";
            lbltr.Text = "";
            lblronu.Text = "Плотность при НУ = ";
            lblronu.Visible = false;
            lblrotr.Text = "Плотность при Tr";
            lblrotr.Visible = false;
            lbln.Text = "Условная оборачиваемость, n/год = ";
            lbln.Visible = false;
            lblkn.Text = "Коэффициент оборачиваемости";
            lblkn.Visible = false;
            lblk0.Text = "Коэффициент оснащенности";
            lblk0.Visible = false;
            lblGc.Text = "Потери (т/период) = ";
            lblGc.Visible = false;
            lblqp.Text = "Удельные потери (кг/тонну) = ";
            lblqp.Visible = false;
            lblGpc.Text = "Потери в единицу времени(г/ с) = ";
            lblGpc.Visible = false;
            lblcv.Text = "Весовая концентрация насыщенных паров (Cv) при Tr = ";
        }

        private void очиститьДанныеToolStripMenuItem_Click(object sender, EventArgs e)
        {
            ClearC();
        }

        private void начатьСначалаToolStripMenuItem_Click(object sender, EventArgs e)
        {
            ClearC();
            Reservuar.SelectedTab = Reservuar.TabPages["TabPage1"];
            Reservuar.TabPages[0].Enabled = true;
            Reservuar.TabPages[1].Enabled = false;
            Reservuar.TabPages[2].Enabled = false;
            Reservuar.TabPages[3].Enabled = false;
            Reservuar.TabPages[4].Enabled = false;
        }

        private void выходToolStripMenuItem_Click(object sender, EventArgs e)
        {
            Environment.Exit(0);
        }

        private void справочникиToolStripMenuItem_Click(object sender, EventArgs e)
        {
            Form4 newForm = new Form4();
            newForm.Show();
        }

        private void тест1ToolStripMenuItem_Click(object sender, EventArgs e)
        {
            ClearC();
            cbres.Checked = true;
            cbTepl.Checked = true;
            txtta.Text = "25";
            txttg.Text = "27";
            cbSouth.Checked = true;
            cbNazem.Checked = true;
            cbAl.Checked = true;
            txttl.Text = "25,2";
            txtPtl.Text = "381";
            txtPtr.Text = "556";
            txtP38.Text = "618";
            txtPa.Text = "752,5";
            string[] row = new string[] { "0,9", "16" };
            dgv2.Rows.Add(row);
            row = new string[] { "2,8", "30" };
            dgv2.Rows.Add(row);
            row = new string[] { "19,9", "44" };
            dgv2.Rows.Add(row);
            row = new string[] { "28,7", "58" };
            dgv2.Rows.Add(row);
            row = new string[] { "37,5", "72" };
            dgv2.Rows.Add(row);
            row = new string[] { "10,2", "100" };
            dgv2.Rows.Add(row);
            txtVg.Text = "1050000";
            txtVp.Text = "50000";
            cbmer.Checked = true;
            comboosn.SelectedIndex = 3;
            cbLow.Checked = true;
            txtdt.Text = "0,725";
            txttau.Text = "4272";
        }

        private void тест2ToolStripMenuItem_Click(object sender, EventArgs e)
        {
            ClearC();
            cbres.Checked = true;
            cbHol.Checked = true;
            txtta.Text = "-9";
            txttg.Text = "4,3";
            cbMiddle.Checked = true;
            cbNazem.Checked = true;
            cbAl.Checked = true;
            txttl.Text = "23";
            txtPtl.Text = "26,4";
            txtPtr.Text = "5,5";
            txtP38.Text = "50";
            txtPa.Text = "762";
            txtVg.Text = "187500";
            txtVp.Text = "6000";
            cbmer.Checked = true;
            comboosn.SelectedIndex = 1;
            cbHigh.Checked = true;
            txtdt.Text = "0,800";
            txttau.Text = "4380";
            txtcv.Text = "5,5";
        }

        private void тест3ToolStripMenuItem_Click(object sender, EventArgs e)
        {
            ClearC();
            cbcist.Checked = true;
            cbTepl.Checked = true;
            txtta.Text = "25";
            txttg.Text = "27";
            cbSouth.Checked = true;
            cbNazem.Checked = true;
            cbBlack.Checked = true;
            txttl.Text = "25,2";
            txtPtl.Text = "381";
            txtPtr.Text = "528";
            txtP38.Text = "618";
            txtPa.Text = "752,5";
            string[] row = new string[] { "0,9", "16" };
            dgv2.Rows.Add(row);
            row = new string[] { "2,8", "30" };
            dgv2.Rows.Add(row);
            row = new string[] { "19,9", "44" };
            dgv2.Rows.Add(row);
            row = new string[] { "28,7", "58" };
            dgv2.Rows.Add(row);
            row = new string[] { "37,5", "72" };
            dgv2.Rows.Add(row);
            row = new string[] { "10,2", "100" };
            dgv2.Rows.Add(row);
            txtVg.Text = "1050000";
            txtVp.Text = "50000";
            cbmer.Checked = true;
            comboosn.SelectedIndex = 3;
            cbLow.Checked = true;
            txtdt.Text = "0,725";
            txttau.Text = "380";
        }

        private void cbHigh_CheckedChanged_1(object sender, EventArgs e)
        {
            if (cbHigh.Checked)
            {
                txtcv.Visible = true;
                lblcv.Visible = true;
                lblPtr.Visible = false;
                txtPtr.Visible = false;
            }
            else
            {
                txtcv.Visible = false;
                lblcv.Visible = false;
                lblPtr.Visible = true;
                txtPtr.Visible = true;
            }
        }

        private void cbcist_CheckedChanged(object sender, EventArgs e)
        {
            if (cbcist.Checked)
            {
                label5.Visible = false;
                panel3.Visible = false;
                cbEmal.Enabled = false;
            }
            else
            {
                label5.Visible = true;
                panel3.Visible = true;
                cbEmal.Enabled = true;
            }
        }

        private void cbmer_CheckedChanged(object sender, EventArgs e)
        {
            comboosn.Items.Clear();
            comboosn.Enabled = true;
            comboosn.Items.Add("не оборудован понтоном, имеет открытый люк или снятый дыхательный клапан");
            comboosn.Items.Add("открытых люков нет, непримерзающие клапаны, обеспечивающие избыточное давление до 200 мм вод.ст.");
            comboosn.Items.Add("открытых люков нет, непримерзающие клапаны, обеспечивающие избыточное давление более 200 мм вод.ст.");
            comboosn.Items.Add("оборудован понтоном");
            comboosn.Items.Add("оборудован плавающей крышей");
            comboosn.Items.Add("включен в газоуравнительную систему резервуаров, у которых совпадение откачки и закачки продукта от 100 до 90%");
            comboosn.Items.Add("включен в газоуравнительную систему резервуаров, у которых совпадение откачки и закачки продукта от 90 до 80%");
            comboosn.Items.Add("включен в газоуравнительную систему резервуаров, у которых совпадение откачки и закачки продукта от от 80 до 70%");
            comboosn.Items.Add("включен в газоуравнительную систему резервуаров, у которых совпадение откачки и закачки продукта от 70 до 50%");
            comboosn.Items.Add("включен в газоуравнительную систему резервуаров, у которых совпадение откачки и закачки продукта от 50 до 30%");
            comboosn.Items.Add("включен в газоуравнительную систему резервуаров, у которых совпадение откачки и закачки продукта менее 30%");
        }

        private void cbbuff_CheckedChanged(object sender, EventArgs e)
        {
            comboosn.Items.Clear();
            comboosn.Enabled = true;
            comboosn.Items.Add("не оборудован понтоном, имеет открытый люк или снятый дыхательный клапан");
            comboosn.Items.Add("открытых люков нет, непримерзающие клапаны, обеспечивающие избыточное давление до 200 мм вод.ст.");
            comboosn.Items.Add("открытых люков нет, непримерзающие клапаны, обеспечивающие избыточное давление более 200 мм вод.ст.");
            comboosn.Items.Add("оборудован понтоном");
            comboosn.Items.Add("оборудован плавающей крышей");
        }

        private void cbMiddle_MouseEnter(object sender, EventArgs e)
        {
            toolTip1.SetToolTip(cbMiddle, "Районы, не вошедшие в южную и северную зоны.");
        }
        
    }
}
