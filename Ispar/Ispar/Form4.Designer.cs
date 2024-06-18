
namespace Ispar
{
    partial class Form4
    {
        /// <summary>
        /// Required designer variable.
        /// </summary>
        private System.ComponentModel.IContainer components = null;

        /// <summary>
        /// Clean up any resources being used.
        /// </summary>
        /// <param name="disposing">true if managed resources should be disposed; otherwise, false.</param>
        protected override void Dispose(bool disposing)
        {
            if (disposing && (components != null))
            {
                components.Dispose();
            }
            base.Dispose(disposing);
        }

        #region Windows Form Designer generated code

        /// <summary>
        /// Required method for Designer support - do not modify
        /// the contents of this method with the code editor.
        /// </summary>
        private void InitializeComponent()
        {
            System.ComponentModel.ComponentResourceManager resources = new System.ComponentModel.ComponentResourceManager(typeof(Form4));
            this.combospr = new System.Windows.Forms.ComboBox();
            this.pb = new System.Windows.Forms.PictureBox();
            ((System.ComponentModel.ISupportInitialize)(this.pb)).BeginInit();
            this.SuspendLayout();
            // 
            // combospr
            // 
            this.combospr.FormattingEnabled = true;
            this.combospr.Items.AddRange(new object[] {
            "Значения коэффициентов К1, К2, К3, в зависимости от Tж",
            "Значения коэффициентов К4 для различных климатических зон",
            "Значения молекулярной массы паров нефтепродуктов М зависимости от T кипения",
            "Значение коэффициента Кn в зависимости от давления паров и годовой оборачиваемост" +
                "и",
            "Значение коэффициента К0 в зависимости от технической оснащенности и режима экспл" +
                "уатации",
            "Диапазон и предельно-допустимые погрешности измерений"});
            this.combospr.Location = new System.Drawing.Point(13, 13);
            this.combospr.Name = "combospr";
            this.combospr.Size = new System.Drawing.Size(975, 29);
            this.combospr.TabIndex = 0;
            this.combospr.SelectedIndexChanged += new System.EventHandler(this.combospr_SelectedIndexChanged);
            // 
            // pb
            // 
            this.pb.Location = new System.Drawing.Point(13, 48);
            this.pb.Name = "pb";
            this.pb.Size = new System.Drawing.Size(975, 531);
            this.pb.SizeMode = System.Windows.Forms.PictureBoxSizeMode.Zoom;
            this.pb.TabIndex = 1;
            this.pb.TabStop = false;
            // 
            // Form4
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(10F, 21F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.ClientSize = new System.Drawing.Size(1000, 591);
            this.Controls.Add(this.pb);
            this.Controls.Add(this.combospr);
            this.Font = new System.Drawing.Font("Century Gothic", 10.2F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(204)));
            this.Icon = ((System.Drawing.Icon)(resources.GetObject("$this.Icon")));
            this.Margin = new System.Windows.Forms.Padding(4, 4, 4, 4);
            this.Name = "Form4";
            this.Text = "Справочники";
            ((System.ComponentModel.ISupportInitialize)(this.pb)).EndInit();
            this.ResumeLayout(false);

        }

        #endregion

        private System.Windows.Forms.ComboBox combospr;
        private System.Windows.Forms.PictureBox pb;
    }
}