import { useState, useEffect, useRef } from "react";
import { AreaChart, Area, BarChart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer, PieChart, Pie, Cell } from "recharts";

const trendData = [
  { day: "1 Feb", masuk: 42, selesai: 38 },
  { day: "5 Feb", masuk: 58, selesai: 45 },
  { day: "10 Feb", masuk: 35, selesai: 52 },
  { day: "15 Feb", masuk: 71, selesai: 60 },
  { day: "20 Feb", masuk: 48, selesai: 44 },
  { day: "25 Feb", masuk: 63, selesai: 58 },
  { day: "1 Mar", masuk: 55, selesai: 49 },
  { day: "5 Mar", masuk: 80, selesai: 65 },
  { day: "8 Mar", masuk: 67, selesai: 70 },
];

const bagianData = [
  { name: "SKH", total: 427, terlambat: 12, warna: "#EC4899" },
  { name: "TAN", total: 336, terlambat: 5, warna: "#10B981" },
  { name: "TEP", total: 208, terlambat: 8, warna: "#6366F1" },
  { name: "SDM", total: 190, terlambat: 3, warna: "#8B5CF6" },
  { name: "DPM", total: 320, terlambat: 7, warna: "#22C55E" },
  { name: "AKN", total: 30, terlambat: 0, warna: "#7C3AED" },
];

const donutData = [
  { name: "Sudah Dibayar", value: 1250, color: "#10B981" },
  { name: "Belum Dibayar", value: 285, color: "#F59E0B" },
  { name: "Siap Bayar", value: 0, color: "#3B82F6" },
];

function AnimatedNumber({ target, duration = 1200, prefix = "", suffix = "" }) {
  const [current, setCurrent] = useState(0);
  const startTime = useRef(null);

  useEffect(() => {
    const animate = (timestamp) => {
      if (!startTime.current) startTime.current = timestamp;
      const progress = Math.min((timestamp - startTime.current) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      setCurrent(Math.floor(eased * target));
      if (progress < 1) requestAnimationFrame(animate);
    };
    requestAnimationFrame(animate);
  }, [target, duration]);

  return (
    <span>{prefix}{current.toLocaleString("id-ID")}{suffix}</span>
  );
}

const CustomTooltip = ({ active, payload, label }) => {
  if (active && payload && payload.length) {
    return (
      <div style={{
        background: "rgba(15,23,42,0.95)", border: "1px solid rgba(255,255,255,0.1)",
        borderRadius: 10, padding: "10px 14px", fontSize: 12
      }}>
        <p style={{ color: "#94a3b8", marginBottom: 6 }}>{label}</p>
        {payload.map((p, i) => (
          <p key={i} style={{ color: p.color, margin: "2px 0" }}>
            {p.name}: <strong>{p.value}</strong>
          </p>
        ))}
      </div>
    );
  }
  return null;
};

export default function Dashboard() {
  const [greeting, setGreeting] = useState("");
  const hour = new Date().getHours();

  useEffect(() => {
    if (hour < 12) setGreeting("Selamat Pagi");
    else if (hour < 15) setGreeting("Selamat Siang");
    else if (hour < 18) setGreeting("Selamat Sore");
    else setGreeting("Selamat Malam");
  }, []);

  const statCards = [
    { label: "Total Dokumen", value: 1535, change: +12, color: "#0D9488", icon: "📄" },
    { label: "Belum Dibayar", value: 285, change: -8, color: "#F59E0B", icon: "⏳", warn: true },
    { label: "Siap Dibayar", value: 0, change: 0, color: "#3B82F6", icon: "✅" },
    { label: "Sudah Dibayar", value: 1250, change: +18, color: "#10B981", icon: "💳" },
  ];

  return (
    <div style={{
      fontFamily: "'DM Sans', 'Segoe UI', sans-serif",
      background: "#F0F4F8",
      minHeight: "100vh",
      padding: "0 0 40px",
      color: "#1e293b"
    }}>
      {/* HEADER BANNER */}
      <div style={{
        background: "linear-gradient(135deg, #0D4F4A 0%, #0D9488 60%, #14B8A6 100%)",
        padding: "28px 36px 24px",
        position: "relative",
        overflow: "hidden"
      }}>
        {/* decorative circles */}
        {[...Array(3)].map((_, i) => (
          <div key={i} style={{
            position: "absolute",
            borderRadius: "50%",
            border: "1px solid rgba(255,255,255,0.08)",
            width: 180 + i * 120,
            height: 180 + i * 120,
            right: -60 + i * -20,
            top: -60 + i * -20,
          }} />
        ))}

        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start", position: "relative" }}>
          <div>
            <div style={{ display: "flex", alignItems: "center", gap: 10, marginBottom: 4 }}>
              <span style={{ fontSize: 20 }}>📊</span>
              <span style={{ color: "rgba(255,255,255,0.65)", fontSize: 13 }}>
                {greeting}, <strong style={{ color: "white" }}>Kabag Keuangan</strong>
              </span>
            </div>
            <h1 style={{ color: "white", fontSize: 26, fontWeight: 700, margin: "0 0 4px" }}>
              Dashboard Kabag Keuangan
            </h1>
            <p style={{ color: "rgba(255,255,255,0.6)", margin: 0, fontSize: 13 }}>
              Pantau dan kelola semua dokumen perusahaan dengan mudah
            </p>
          </div>

          {/* Summary hari ini */}
          <div style={{
            background: "rgba(255,255,255,0.12)",
            backdropFilter: "blur(10px)",
            borderRadius: 12,
            padding: "12px 18px",
            border: "1px solid rgba(255,255,255,0.2)",
            fontSize: 12,
            color: "white",
            minWidth: 240
          }}>
            <div style={{ fontWeight: 600, marginBottom: 8, color: "rgba(255,255,255,0.8)" }}>
              ☀️ Ringkasan Hari Ini
            </div>
            {[
              { dot: "#10B981", text: "67 dokumen baru masuk" },
              { dot: "#F59E0B", text: "12 mendekati batas waktu" },
              { dot: "#EF4444", text: "5 dokumen terlambat" },
            ].map((item, i) => (
              <div key={i} style={{ display: "flex", alignItems: "center", gap: 8, marginBottom: 4 }}>
                <div style={{ width: 7, height: 7, borderRadius: "50%", background: item.dot, flexShrink: 0 }} />
                <span style={{ color: "rgba(255,255,255,0.85)" }}>{item.text}</span>
              </div>
            ))}
          </div>
        </div>
      </div>

      <div style={{ padding: "24px 36px 0" }}>

        {/* STAT CARDS */}
        <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(200px, 1fr))", gap: 16, marginBottom: 24 }}>
          {statCards.map((s, i) => (
            <div key={i} style={{
              background: "white",
              borderRadius: 14,
              padding: "18px 20px",
              boxShadow: "0 1px 4px rgba(0,0,0,0.06)",
              borderTop: `3px solid ${s.color}`,
              position: "relative",
              overflow: "hidden"
            }}>
              <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start" }}>
                <div>
                  <div style={{ fontSize: 11, color: "#94a3b8", fontWeight: 600, textTransform: "uppercase", letterSpacing: 0.5, marginBottom: 6 }}>
                    {s.label}
                  </div>
                  <div style={{ fontSize: 30, fontWeight: 800, color: "#0f172a", lineHeight: 1 }}>
                    <AnimatedNumber target={s.value} />
                  </div>
                  <div style={{
                    marginTop: 8, fontSize: 11, fontWeight: 600,
                    color: s.change > 0 ? "#10B981" : s.change < 0 ? "#EF4444" : "#94a3b8"
                  }}>
                    {s.change > 0 ? `↑ +${s.change}%` : s.change < 0 ? `↓ ${s.change}%` : "→ Sama"} vs bulan lalu
                  </div>
                </div>
                <div style={{
                  width: 44, height: 44, borderRadius: 12,
                  background: s.color + "18",
                  display: "flex", alignItems: "center", justifyContent: "center",
                  fontSize: 20
                }}>
                  {s.icon}
                </div>
              </div>
              <div style={{ marginTop: 12, fontSize: 12, color: "#0D9488", cursor: "pointer", fontWeight: 500 }}>
                Lihat Detail →
              </div>
            </div>
          ))}

          {/* Total Nilai */}
          <div style={{
            background: "linear-gradient(135deg, #0D4F4A, #0D9488)",
            borderRadius: 14,
            padding: "18px 20px",
            boxShadow: "0 1px 4px rgba(0,0,0,0.1)",
            color: "white"
          }}>
            <div style={{ fontSize: 11, color: "rgba(255,255,255,0.65)", fontWeight: 600, textTransform: "uppercase", letterSpacing: 0.5, marginBottom: 6 }}>
              Total Nilai (RP)
            </div>
            <div style={{ fontSize: 20, fontWeight: 800, lineHeight: 1.2 }}>
              <AnimatedNumber target={402664464914} prefix="Rp " />
            </div>
            <div style={{ marginTop: 8, fontSize: 11, color: "rgba(255,255,255,0.7)", fontWeight: 600 }}>
              ↑ +22% vs bulan lalu
            </div>
            <div style={{ marginTop: 12, fontSize: 20 }}>💰</div>
          </div>
        </div>

        {/* CHARTS ROW */}
        <div style={{ display: "grid", gridTemplateColumns: "2fr 1fr", gap: 16, marginBottom: 24 }}>

          {/* Area Chart Tren */}
          <div style={{ background: "white", borderRadius: 14, padding: "20px 24px", boxShadow: "0 1px 4px rgba(0,0,0,0.06)" }}>
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 18 }}>
              <div>
                <h3 style={{ margin: 0, fontSize: 15, fontWeight: 700, color: "#0f172a" }}>Tren Dokumen 30 Hari</h3>
                <p style={{ margin: "2px 0 0", fontSize: 12, color: "#94a3b8" }}>Dokumen masuk vs diselesaikan</p>
              </div>
              <div style={{ display: "flex", gap: 16, fontSize: 11, color: "#64748b" }}>
                <span><span style={{ color: "#0D9488" }}>●</span> Masuk</span>
                <span><span style={{ color: "#F59E0B" }}>●</span> Selesai</span>
              </div>
            </div>
            <ResponsiveContainer width="100%" height={180}>
              <AreaChart data={trendData}>
                <defs>
                  <linearGradient id="gMasuk" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="5%" stopColor="#0D9488" stopOpacity={0.15} />
                    <stop offset="95%" stopColor="#0D9488" stopOpacity={0} />
                  </linearGradient>
                  <linearGradient id="gSelesai" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="5%" stopColor="#F59E0B" stopOpacity={0.15} />
                    <stop offset="95%" stopColor="#F59E0B" stopOpacity={0} />
                  </linearGradient>
                </defs>
                <XAxis dataKey="day" tick={{ fontSize: 10, fill: "#94a3b8" }} axisLine={false} tickLine={false} />
                <YAxis tick={{ fontSize: 10, fill: "#94a3b8" }} axisLine={false} tickLine={false} />
                <Tooltip content={<CustomTooltip />} />
                <Area type="monotone" dataKey="masuk" name="Masuk" stroke="#0D9488" strokeWidth={2} fill="url(#gMasuk)" dot={false} />
                <Area type="monotone" dataKey="selesai" name="Selesai" stroke="#F59E0B" strokeWidth={2} fill="url(#gSelesai)" dot={false} />
              </AreaChart>
            </ResponsiveContainer>
          </div>

          {/* Donut Chart */}
          <div style={{ background: "white", borderRadius: 14, padding: "20px 24px", boxShadow: "0 1px 4px rgba(0,0,0,0.06)" }}>
            <h3 style={{ margin: "0 0 4px", fontSize: 15, fontWeight: 700, color: "#0f172a" }}>Proporsi Status</h3>
            <p style={{ margin: "0 0 12px", fontSize: 12, color: "#94a3b8" }}>Distribusi status pembayaran</p>
            <div style={{ display: "flex", justifyContent: "center" }}>
              <PieChart width={160} height={160}>
                <Pie data={donutData} cx={75} cy={75} innerRadius={45} outerRadius={70}
                  dataKey="value" strokeWidth={2} stroke="white">
                  {donutData.map((d, i) => <Cell key={i} fill={d.color} />)}
                </Pie>
                <Tooltip formatter={(v) => v.toLocaleString("id-ID")} />
              </PieChart>
            </div>
            <div style={{ display: "flex", flexDirection: "column", gap: 6, marginTop: 4 }}>
              {donutData.map((d, i) => (
                <div key={i} style={{ display: "flex", justifyContent: "space-between", alignItems: "center", fontSize: 12 }}>
                  <div style={{ display: "flex", alignItems: "center", gap: 6 }}>
                    <div style={{ width: 10, height: 10, borderRadius: 3, background: d.color }} />
                    <span style={{ color: "#64748b" }}>{d.name}</span>
                  </div>
                  <strong style={{ color: "#0f172a" }}>{d.value.toLocaleString("id-ID")}</strong>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* BAGIAN + BAR CHART */}
        <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 16, marginBottom: 24 }}>

          {/* Bar chart per bagian */}
          <div style={{ background: "white", borderRadius: 14, padding: "20px 24px", boxShadow: "0 1px 4px rgba(0,0,0,0.06)" }}>
            <h3 style={{ margin: "0 0 4px", fontSize: 15, fontWeight: 700, color: "#0f172a" }}>Volume per Bagian</h3>
            <p style={{ margin: "0 0 16px", fontSize: 12, color: "#94a3b8" }}>Total dokumen dan keterlambatan</p>
            <ResponsiveContainer width="100%" height={180}>
              <BarChart data={bagianData} barSize={18}>
                <XAxis dataKey="name" tick={{ fontSize: 11, fill: "#94a3b8" }} axisLine={false} tickLine={false} />
                <YAxis tick={{ fontSize: 10, fill: "#94a3b8" }} axisLine={false} tickLine={false} />
                <Tooltip content={<CustomTooltip />} />
                <Bar dataKey="total" name="Total" radius={[4,4,0,0]}>
                  {bagianData.map((d, i) => <Cell key={i} fill={d.warna} />)}
                </Bar>
              </BarChart>
            </ResponsiveContainer>
          </div>

          {/* Dokumen per Bagian cards */}
          <div style={{ background: "white", borderRadius: 14, padding: "20px 24px", boxShadow: "0 1px 4px rgba(0,0,0,0.06)" }}>
            <h3 style={{ margin: "0 0 4px", fontSize: 15, fontWeight: 700, color: "#0f172a" }}>Dokumen per Bagian</h3>
            <p style={{ margin: "0 0 14px", fontSize: 12, color: "#94a3b8" }}>Dengan indikator keterlambatan</p>
            <div style={{ display: "flex", flexDirection: "column", gap: 8 }}>
              {bagianData.map((b, i) => (
                <div key={i} style={{ display: "flex", alignItems: "center", gap: 12 }}>
                  <div style={{
                    width: 32, height: 32, borderRadius: 8,
                    background: b.warna + "20",
                    display: "flex", alignItems: "center", justifyContent: "center",
                    fontSize: 11, fontWeight: 700, color: b.warna, flexShrink: 0
                  }}>
                    {b.name}
                  </div>
                  <div style={{ flex: 1 }}>
                    <div style={{ display: "flex", justifyContent: "space-between", marginBottom: 4 }}>
                      <span style={{ fontSize: 12, fontWeight: 600, color: "#0f172a" }}>{b.total} dokumen</span>
                      {b.terlambat > 0 && (
                        <span style={{ fontSize: 11, color: "#EF4444", fontWeight: 600 }}>
                          ⚠ {b.terlambat} terlambat
                        </span>
                      )}
                    </div>
                    <div style={{ height: 5, background: "#f1f5f9", borderRadius: 99, overflow: "hidden" }}>
                      <div style={{
                        height: "100%", borderRadius: 99,
                        width: `${Math.min((b.total / 427) * 100, 100)}%`,
                        background: b.terlambat > 0
                          ? `linear-gradient(90deg, ${b.warna}, #EF4444)`
                          : b.warna,
                        transition: "width 1s ease"
                      }} />
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* BOTTOM - Bagian Cards Grid (existing) */}
        <div style={{ background: "white", borderRadius: 14, padding: "20px 24px", boxShadow: "0 1px 4px rgba(0,0,0,0.06)" }}>
          <div style={{ display: "flex", alignItems: "center", gap: 8, marginBottom: 16 }}>
            <span style={{ fontSize: 16 }}>🗂</span>
            <h3 style={{ margin: 0, fontSize: 15, fontWeight: 700, color: "#0f172a" }}>Dokumen per Bagian</h3>
          </div>
          <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill, minmax(130px, 1fr))", gap: 12 }}>
            {[
              { name: "AKN", count: 30, icon: "🧮", color: "#7C3AED" },
              { name: "DPM", count: 320, icon: "📈", color: "#22C55E" },
              { name: "KPL", count: 0, icon: "👑", color: "#F59E0B" },
              { name: "PMO", count: 3, icon: "📋", color: "#06B6D4" },
              { name: "SDM", count: 190, icon: "👥", color: "#8B5CF6" },
              { name: "SKH", count: 427, icon: "🏢", color: "#EC4899" },
              { name: "TAN", count: 336, icon: "🌿", color: "#10B981" },
              { name: "TEP", count: 208, icon: "⚙️", color: "#6366F1" },
              { name: "PTI", count: 19, icon: "🖥", color: "#3B82F6" },
            ].map((b, i) => (
              <div key={i} style={{
                borderRadius: 12, padding: "14px",
                background: "#F8FAFC",
                border: "1px solid #E2E8F0",
                cursor: "pointer",
                transition: "all 0.15s ease",
              }}
                onMouseEnter={e => {
                  e.currentTarget.style.background = b.color + "12"
                  e.currentTarget.style.borderColor = b.color + "40"
                  e.currentTarget.style.transform = "translateY(-2px)"
                }}
                onMouseLeave={e => {
                  e.currentTarget.style.background = "#F8FAFC"
                  e.currentTarget.style.borderColor = "#E2E8F0"
                  e.currentTarget.style.transform = "translateY(0)"
                }}
              >
                <div style={{
                  width: 38, height: 38, borderRadius: 10,
                  background: b.color + "20",
                  display: "flex", alignItems: "center", justifyContent: "center",
                  fontSize: 18, marginBottom: 10
                }}>
                  {b.icon}
                </div>
                <div style={{ fontWeight: 700, fontSize: 13, color: "#0f172a" }}>{b.name}</div>
                <div style={{ fontSize: 18, fontWeight: 800, color: b.color, margin: "2px 0" }}>
                  {b.count.toLocaleString("id-ID")}
                </div>
                <div style={{ fontSize: 11, color: "#94a3b8" }}>dokumen</div>
                <div style={{ marginTop: 8, fontSize: 11, color: b.color, fontWeight: 500 }}>Lihat →</div>
              </div>
            ))}
          </div>
        </div>

      </div>
    </div>
  );
}
