<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 

$host = 'localhost';
$db   = 'erpspine_spine';
$user = 'erpspine_spine';
$pass = 'Hesoyamin100%';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

$numbers = array(571,574,575,537,519,580,582,585,586,591,595,598,596,594,615,625,632,451,649,659,668);

foreach($numbers as $num){

  //$stmt = $pdo->prepare('SELECT tid, bill_id,method,refer_no,transaction_date,user_id,note,ins,created_at,updated_at  FROM rose_transactions WHERE id = :id');

  $stmt = $pdo->prepare('SELECT  id, tid, invoicedate, invoiceduedate, subtotal,tax,total,refer,notes,customer_id,user_id,ins,notes,tax_id FROM rose_invoices WHERE refer = :refer');
$stmt->execute(['refer' => $num]);
$row_two = $stmt->fetch();

//$stmt->execute(['id' => $num]);

//$row_one = $stmt->fetch();
//$id = $row_one['bill_id'];





 //echo $row_two['id']." ". $row_two['tid'] ." ". $row_two['invoicedate'] ." ".$row_two['subtotal']." ".$row_two['tax']."  ".$row_two['refer'].  "<br />";

// debit
$data = [
    'tid' => $row_two['tid'],
    'payer_type' => 'customer',
    'payer_id' =>0,
    'for_who' =>$row_two['customer_id'],
    'account_id' => 30,
    'trans_category_id' => 1,
    'transaction_type' =>'sales',
    'user_id' => $row_two['user_id'],
    'note' =>$row_two['notes'],
    'ins' => $row_two['ins'],
    'refer_no' => $row_two['refer'],
    'invoice_id' =>$row_two['id'],
    'is_bill' => 0,
    'credit' => $row_two['tax'],
    'total_amount' => $row_two['total'],
    'taxformat' => $row_two['tax_id'],
    'transaction_date' => $row_two['invoicedate'],
    'due_date' => $row_two['invoiceduedate'],
    'requested_by' => 0,
    'approved_by' => 0,
    
    
    
    
];





$sql = "INSERT INTO rose_transactions (tid, payer_type, payer_id, for_who, account_id, trans_category_id, transaction_type, user_id, note, ins, refer_no, invoice_id, is_bill, credit, total_amount, taxformat,transaction_date, due_date,requested_by,approved_by ) VALUES (:tid, :payer_type, :payer_id, :for_who, :account_id, :trans_category_id, :transaction_type, :user_id, :note, :ins, :refer_no, :invoice_id, :is_bill, :credit,:total_amount,:taxformat,:transaction_date,:due_date,:requested_by,:approved_by)";
  $stmt= $pdo->prepare($sql);

   $save=$stmt->execute($data);

echo  $save;
}



/*$stmt = $pdo->prepare('SELECT refer_no, tax_type, id, tid, payer_id, bill_id, is_bill, is_bill, account_id, trans_category_id, credit, debit, method, payer_type FROM rose_transactions WHERE     credit > :credit   AND is_bill=:is_bill AND transaction_type=:transaction_type   ' );

$stmt->execute(['is_bill'=> 1, 'credit' => 0, 'transaction_type' => 'purchases' ]);



while ($row = $stmt->fetch())
{


$stmt = $pdo->prepare('SELECT refer_no, tax_type, id, tid, payer_id, bill_id, is_bill, is_bill, account_id, trans_category_id, credit, debit, method, payer_type FROM rose_transactions  WHERE  bill_id = :bill_id ' );
$stmt->execute(['bill_id'=> $row['id']]);


while ($rows = $stmt->fetch())
{


   echo $row['id']." ". $rows['bill_id'] ." ". $rows['credit'] ." ".$rows['refer_no']." ".$rows['payer_id'].  "<br />";

 }

  }
*/

/*
$stmt = $pdo->prepare('SELECT  refer, id, tid, total, status, tax FROM rose_invoices ' );
$stmt->execute();

while ($row = $stmt->fetch())
{

  $stmts = $pdo->prepare('SELECT  refer_no, credit, for_who, tid,bill_id, payer_id, credit,debit, is_bill, tax_type, account_id, trans_category_id, credit, method FROM rose_transactions WHERE payer_id > :payer_id  AND credit > :credit AND tid=:tid  AND transaction_type=:transaction_type' );
$stmts->execute(['payer_id' => 0, 'credit' => 0, 'tid' => $row['tid'], 'transaction_type' => 'sales']);
while ($rows = $stmts->fetch())
{

   echo $rows['id']." ". $rows['credit'] ." ". $rows['debit'] ." ".$rows['refer_no']." ".$row['payer_id'].  "<br />";



  }
*/



//}


//$stmt = $pdo->prepare('SELECT  tax_type, id, tid, payer_id, bill_id, is_bill, is_bill, account_id, trans_category_id, credit, debit, method, payer_type FROM rose_transactions WHERE  bill_id > :bill_id AND payer_id = :payer_id AND tax_type !=:tax_type  AND credit > :credit   AND transaction_type=:transaction_type   ' );
//$stmt->execute(['bill_id'=> 0, 'payer_id'=> 0, 'tax_type' => 'sales_purchases ', 'credit' => 0,   'transaction_type' => 'sales']);

//while ($row = $stmt->fetch())
//{

  // echo $row['id']."  ". $row['tid'] ." ". $row['credit'] ." ". $row['debit'] ." ".$row['tax_type']." ".$row['bill_id'].  "<br />";

  //$sql = "UPDATE rose_transactions SET  for_who=? WHERE id = ?";
  //$result=$pdo->prepare($sql)->execute([$row['payer_id'], $row['id']]);
 //echo $result;



/*$stmts = $pdo->prepare('SELECT  id, for_who, tid,bill_id, payer_id, credit,debit, is_bill, tax_type, account_id, trans_category_id, credit, method FROM rose_transactions WHERE account_id != :account_id AND payer_id = :payer_id AND transaction_type=:transaction_type' );
$stmts->execute(['account_id' => 22, 'payer_id' => $row['payer_id'], 'transaction_type' => '']);
while ($rows = $stmts->fetch())
{

  // echo $rows['id']." ". $rows['credit'] ." ". $rows['debit'] ." ".$rows['tax_type']." ".$row['payer_id'].  "<br />";

  // $sql = "DELETE FROM  rose_transactions  WHERE id = ?";
//$result=$pdo->prepare($sql)->execute([$rows['id']]);
//echo $result;


 $sql = "UPDATE rose_transactions SET  payer_id=?,transaction_type=? WHERE id = ?";
$result=$pdo->prepare($sql)->execute([0,'sales', $rows['id']]);
echo $result;
 //  echo $rows['id']." ". $rows['credit'] ." ". $rows['debit'] ." ".$rows['tax_type']." ".$row['payer_id'].  "<br />";

  }*/

//}


/*
$stmt = $pdo->prepare('SELECT  tax_type, id, tid, payer_id, is_bill, is_bill, account_id, trans_category_id, credit, method,payer_type FROM rose_transactions WHERE credit > :credit AND is_bill = :is_bill  AND transaction_type=:transaction_type ' );
$stmt->execute(['credit' => 0, 'is_bill' => 1,  'transaction_type' => 'purchases']);

while ($row = $stmt->fetch())
{

$stmts = $pdo->prepare('SELECT  id, tid, payer_id, credit, is_bill, tax_type, account_id, trans_category_id, credit, method FROM rose_transactions WHERE  tid = :tid AND credit > :credit   AND tax_type=:tax_type' );
$stmts->execute(['tid' => $row['tid'], 'credit' => 0, 'tax_type' => 'sales_purchases']);
while ($rows = $stmts->fetch())
{

  $sql = "UPDATE rose_transactions SET  credit=? WHERE id = ?";
  $result=$pdo->prepare($sql)->execute([0, $rows['id']]);
 echo $result;
   // echo $rows['id']." ". $rows['credit'] ." ".$rows['tax_type']." ".$row['payer_id'].  "<br />";

  }

}*/



/*$data=array();
$stmt = $pdo->prepare('SELECT  tax_type, id, tid, is_bill, account_id, trans_category_id, credit, method FROM rose_transactions WHERE credit > :credit AND is_bill = :is_bill  AND transaction_type=:transaction_type' );
$stmt->execute(['credit' => 0, 'is_bill' => 1,  'transaction_type' => 'purchases']);

while ($row = $stmt->fetch())
{

  $stmt = $pdo->prepare('SELECT  tid, is_bill, account_id, trans_category_id, credit, tax_type FROM rose_transactions WHERE tid = :tid AND tax_type = :tax_type ');
   $stmt->execute(['tid' => $row['tid'], 'tax_type' =>'sales_purchases']);
//$row_two = $stmt->fetch();
//if($row_two ){

  echo $row['tid']." ". $row['credit'] ." ".$row['tax_type'].  "<br />";

//}



 /* $sql = "UPDATE rose_transactions SET expense_grandtotal = ? WHERE id = ?";
  $result=$pdo->prepare($sql)->execute([$row['credit'], $row['id']]);
 echo $result;*/

     


//}









/*
$stmt = $pdo->prepare('SELECT  id, tid, is_bill, account_id, trans_category_id, credit, method FROM rose_transactions WHERE credit > :credit AND payer_id > :payer_id AND  bill_id > :bill_id AND transaction_type!=:transaction_type' );
$stmt->execute(['credit' => 0, 'payer_id' => 0, 'bill_id' => 0, 'transaction_type' => 'sales']);

while ($row = $stmt->fetch())
{

  $sql = "UPDATE rose_transactions SET payer_id = ? WHERE id = ?";
  $result=$pdo->prepare($sql)->execute([0, $row['id']]);
 echo $result;

    //echo $row['tid']." ". $row['credit'] ." ".$row['method'].  "<br />";

$stmts = $pdo->prepare('SELECT  id, tid, payer_id, debit, is_bill, account_id, trans_category_id, credit, method FROM rose_transactions WHERE tid = :tid AND debit > :debit   AND transaction_type!=:transaction_type' );
$stmts->execute(['tid' => $row['tid'], 'debit' => 0, 'transaction_type' => 'sales']);
while ($rows = $stmts->fetch())
{

  $sql = "UPDATE rose_transactions SET payer_id = ? WHERE id = ?";
  $result=$pdo->prepare($sql)->execute([$rows['payer_id'], $rows['id']]);
 echo $result;
    //echo $rows['id']." ". $rows['debit'] ." ".$rows['method'].  "<br />";

  }
}*/






/*$numbers = array(20 ,23,26,29,32,35,38,41,44,47,50,53,56,62,66,69,74,77,80,83,86,89,94,99,101,104,107,112,115,118,121,124,127,130,133,138,141,146,149,152,155,164,167,170,173,175,178,181,184,187,190,193);
foreach($numbers as $num){

  $stmt = $pdo->prepare('SELECT tid, bill_id,method,refer_no,transaction_date,user_id,note,ins,created_at,updated_at  FROM rose_transactions WHERE id = :id');
$stmt->execute(['id' => $num]);

$row_one = $stmt->fetch();
$id = $row_one['bill_id'];


$stmt = $pdo->prepare('SELECT  is_bill, account_id, trans_category_id, credit FROM rose_transactions WHERE id = :id');
$stmt->execute(['id' => $id]);
$row_two = $stmt->fetch();


$data = [
    'tid' => $row_one['tid'],
    'bill_id' => $row_one['bill_id'],
    'method' => $row_one['method'],
    'refer_no' =>$row_one['refer_no'],
    'transaction_date' =>$row_one['transaction_date'],
    'user_id' => $row_one['user_id'],
    'note' =>$row_one['note'],
    'ins' => $row_one['ins'],
    'created_at' => $row_one['created_at'],
    'updated_at' =>$row_one['updated_at'],
    'is_bill' => 0,
    'account_id' => $row_two['account_id'],
    'trans_category_id' => $row_two['trans_category_id'],
    'debit' => $row_two['credit'],
    'payment_date' => $row_one['transaction_date']
    
    
];


$sql = "INSERT INTO rose_transactions (tid, bill_id, method, refer_no, transaction_date, user_id, note, ins, created_at, updated_at, is_bill, account_id, trans_category_id, debit, payment_date  ) VALUES (:tid, :bill_id, :method, :refer_no, :transaction_date, :user_id, :note, :ins, :created_at, :updated_at, :is_bill, :account_id, :trans_category_id, :debit, :payment_date)";
  $stmt= $pdo->prepare($sql);

   $stmt->execute($data);


}

*/

//print_r($data);





/*
try {
    $pdo->beginTransaction();
    $stmt->execute($data);
    $pdo->commit();
}catch (Exception $e){
    $pdo->rollback();
    throw $e;
}

if($stmt){
    echo 1;
}else{
    echo 0;
}

*/


//$password = $row[1];

//echo $id;

//$name = $stmt->fetchColumn();


//$user = $stmt->fetch();
/*while ($row = $stmt->fetch())
{
    echo $row['credit'] . "\n";
}*/


?>