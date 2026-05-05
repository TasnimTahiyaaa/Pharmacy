<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/db.php';
requireAdmin();
$db = getDB();

$period = in_array($_GET['period']??'',['week','month','year']) ? $_GET['period'] : 'month';

$dateCond = match($period) {
    'week'  => "AND o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
    'year'  => "AND YEAR(o.created_at)=YEAR(CURDATE())",
    default => "AND MONTH(o.created_at)=MONTH(CURDATE()) AND YEAR(o.created_at)=YEAR(CURDATE())",
};

$income = $db->query("SELECT
  COALESCE(SUM(CASE WHEN o.status!='cancelled' THEN o.total_amount ELSE 0 END),0) as total_income,
  COALESCE(SUM(CASE WHEN o.type='online' AND o.status!='cancelled' THEN o.total_amount ELSE 0 END),0) as online_income,
  COALESCE(SUM(CASE WHEN o.type='offline' AND o.status!='cancelled' THEN o.total_amount ELSE 0 END),0) as offline_income,
  COALESCE(SUM(CASE WHEN o.payment_method='bkash' AND o.status!='cancelled' THEN o.total_amount ELSE 0 END),0) as bkash_income,
  COALESCE(SUM(CASE WHEN o.payment_method='cash' AND o.status!='cancelled' THEN o.total_amount ELSE 0 END),0) as cash_income,
  COUNT(CASE WHEN o.status!='cancelled' THEN 1 END) as total_orders
  FROM orders o WHERE 1=1 $dateCond")->fetch_assoc();

$topMeds = $db->query("SELECT m.name as medicine_name, SUM(oi.quantity) as total_qty, SUM(oi.subtotal) as total_rev
  FROM order_items oi JOIN medicines m ON m.id=oi.medicine_id JOIN orders o ON o.id=oi.order_id
  WHERE o.status!='cancelled' $dateCond GROUP BY oi.medicine_id ORDER BY total_qty DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);

$topCustomers = $db->query("SELECT u.name as customer_name, COUNT(o.id) as total_orders, SUM(o.total_amount) as total_spend
  FROM orders o JOIN users u ON u.id=o.user_id WHERE o.status!='cancelled' $dateCond GROUP BY o.user_id ORDER BY total_spend DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);

$dailyData = $db->query("SELECT DATE(o.created_at) as date,
  SUM(CASE WHEN o.type='online' THEN o.total_amount ELSE 0 END) as online,
  SUM(CASE WHEN o.type='offline' THEN o.total_amount ELSE 0 END) as offline
  FROM orders o WHERE o.status!='cancelled' $dateCond GROUP BY DATE(o.created_at) ORDER BY date")->fetch_all(MYSQLI_ASSOC);

$maxMedQty = $topMeds ? max(array_column($topMeds,'total_qty')) : 1;
$maxCustSpend = $topCustomers ? max(array_column($topCustomers,'total_spend')) : 1;

$pageTitle = 'Reports & Analytics';
include '../includes/admin_header.php';
?>

<div class="admin-page-header">
  <div><h1>Reports & Analytics</h1><p>Business insights and performance metrics.</p></div>
  <form method="GET">
    <select name="period" class="form-control form-select" style="width:160px" onchange="this.form.submit()">
      <option value="week" <?=$period==='week'?'selected':''?>>This Week</option>
      <option value="month" <?=$period==='month'?'selected':''?>>This Month</option>
      <option value="year" <?=$period==='year'?'selected':''?>>This Year</option>
    </select>
  </form>
</div>

<!-- Income Summary Cards -->
<div class="kpi-grid" style="margin-bottom:24px">
  <div class="card kpi-card kpi-primary"><div class="kpi-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 100 7h5a3.5 3.5 0 110 7H6"/></svg></div><div class="kpi-value">৳<?=number_format($income['total_income'],0)?></div><div class="kpi-label">Total Income</div></div>
  <div class="card kpi-card kpi-blue"><div class="kpi-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg></div><div class="kpi-value">৳<?=number_format($income['online_income'],0)?></div><div class="kpi-label">Online Sales</div></div>
  <div class="card kpi-card kpi-green"><div class="kpi-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div><div class="kpi-value">৳<?=number_format($income['offline_income'],0)?></div><div class="kpi-label">Offline Sales</div></div>
  <div class="card kpi-card kpi-purple"><div class="kpi-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></div><div class="kpi-value">৳<?=number_format($income['bkash_income'],0)?></div><div class="kpi-label">bKash Income</div></div>
</div>

<!-- Daily Revenue Trend -->
<?php if (!empty($dailyData)): ?>
<div class="chart-box" style="margin-bottom:24px">
  <h3>Daily Revenue Trend</h3>
  <div style="overflow-x:auto">
    <div style="display:flex;align-items:flex-end;gap:4px;height:160px;padding-top:16px;min-width:<?=max(300,count($dailyData)*40)?>px">
      <?php $maxDay = max(array_map(fn($d)=>$d['online']+$d['offline'],$dailyData),[]); $maxDay=max($maxDay,1); ?>
      <?php foreach($dailyData as $d): $tot=$d['online']+$d['offline']; ?>
      <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;min-width:32px">
        <span style="font-size:9px;color:var(--muted)">৳<?=number_format($tot,0)?></span>
        <div style="width:100%;display:flex;flex-direction:column;gap:1px">
          <div style="background:var(--primary);height:<?=($d['online']/$maxDay*120)?>px;border-radius:4px 4px 0 0"></div>
          <div style="background:#1a8db5;height:<?=($d['offline']/$maxDay*120)?>px"></div>
        </div>
        <span style="font-size:9px;color:var(--muted)"><?=date('M j',strtotime($d['date']))?></span>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="pie-legend" style="justify-content:flex-start;margin-top:8px">
      <div class="pie-legend-item"><div class="pie-dot" style="background:var(--primary)"></div>Online</div>
      <div class="pie-legend-item"><div class="pie-dot" style="background:#1a8db5"></div>Offline</div>
    </div>
  </div>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px">
  <!-- Top Medicines -->
  <div class="chart-box">
    <h3 style="display:flex;align-items:center;gap:8px">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--primary)"><path d="M10.5 20H4a2 2 0 01-2-2V6a2 2 0 012-2h3.9a2 2 0 011.69.9l.81 1.2a2 2 0 001.67.9H20a2 2 0 012 2v2"/></svg>
      Top Selling Medicines
    </h3>
    <?php if(empty($topMeds)): ?>
    <div style="text-align:center;padding:40px;color:var(--muted);font-size:14px">No data for this period</div>
    <?php else: ?>
    <div id="top-meds-bars"></div>
    <div class="top-list" style="margin-top:12px">
      <?php foreach(array_slice($topMeds,0,5) as $i=>$m): ?>
      <div class="top-list-item">
        <div class="top-rank"><?=$i+1?></div>
        <div class="top-info"><p><?=htmlspecialchars($m['medicine_name'])?></p><small><?=$m['total_qty']?> units sold</small></div>
        <div class="top-amount">৳<?=number_format($m['total_rev'],0)?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded',function(){
      drawBars('top-meds-bars',[
        <?php foreach(array_slice($topMeds,0,6) as $m): ?>
        {label:'<?=addslashes(substr($m['medicine_name'],0,15))?>',value:<?=(int)$m['total_qty']?>},
        <?php endforeach; ?>
      ], <?=$maxMedQty?>);
    });
    </script>
    <?php endif; ?>
  </div>

  <!-- Top Customers -->
  <div class="chart-box">
    <h3 style="display:flex;align-items:center;gap:8px">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--primary)"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
      Top Customers
    </h3>
    <?php if(empty($topCustomers)): ?>
    <div style="text-align:center;padding:40px;color:var(--muted);font-size:14px">No data for this period</div>
    <?php else: ?>
    <div class="top-list">
      <?php foreach($topCustomers as $i=>$c): ?>
      <div class="top-list-item">
        <div class="top-rank"><?=$i+1?></div>
        <div class="top-info"><p><?=htmlspecialchars($c['customer_name'])?></p><small><?=$c['total_orders']?> orders</small></div>
        <div class="top-amount">৳<?=number_format($c['total_spend'],2)?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Income Breakdown Pie -->
<div class="chart-box">
  <h3>Income Breakdown by Source</h3>
  <?php $totalPie = $income['online_income']+$income['offline_income']+$income['bkash_income']+$income['cash_income']; ?>
  <?php if($totalPie <= 0): ?>
  <div style="text-align:center;padding:40px;color:var(--muted);font-size:14px">No income data for this period</div>
  <?php else: ?>
  <div style="display:flex;align-items:center;gap:40px;flex-wrap:wrap;justify-content:center">
    <canvas id="incomePie" class="pie-canvas" width="150" height="150"></canvas>
    <div>
      <?php $pieData=[['Online',$income['online_income'],'#1a9e72'],['Offline',$income['offline_income'],'#1a8db5'],['bKash',$income['bkash_income'],'#7c3aed'],['Cash',$income['cash_income'],'#ea580c']];?>
      <?php foreach($pieData as $pd): if($pd[1]<=0) continue; ?>
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
        <div style="width:14px;height:14px;border-radius:4px;background:<?=$pd[2]?>;flex-shrink:0"></div>
        <div>
          <p style="font-size:14px;font-weight:700"><?=$pd[0]?></p>
          <p style="font-size:13px;color:var(--muted)">৳<?=number_format($pd[1],2)?> (<?=round($pd[1]/$totalPie*100)?>%)</p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <script>
  document.addEventListener('DOMContentLoaded',function(){
    drawPie('incomePie',[
      {value:<?=$income['online_income']?>},{value:<?=$income['offline_income']?>},
      {value:<?=$income['bkash_income']?>},{value:<?=$income['cash_income']?>}
    ],['#1a9e72','#1a8db5','#7c3aed','#ea580c']);
  });
  </script>
  <?php endif; ?>
</div>

<?php include '../includes/admin_footer.php'; ?>
