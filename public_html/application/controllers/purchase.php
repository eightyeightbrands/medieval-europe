<?php defined('SYSPATH') OR die('No direct access allowed.');

class Purchase_Controller extends Controller
{
  // Imposto il nome del template da usare

  public function trialpay()
  {
    $message_signature = $_SERVER['HTTP_TRIALPAY_HMAC_MD5'];

    // Recalculate the signature locally
    $key = '86383e4462';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      // the following is for POST notification
      if (empty($HTTP_RAW_POST_DATA)) {
        $recalculated_message_signature = hash_hmac('md5', file_get_contents('php://input'), $key);
      } else {
        $recalculated_message_signature = hash_hmac('md5', $HTTP_RAW_POST_DATA, $key);
      }

    } else {
      // the following is for GET notification
      $recalculated_message_signature = hash_hmac('md5', $_SERVER['QUERY_STRING'], $key);
    }

    if ($message_signature == $recalculated_message_signature) {
      kohana::log('debug', 'message is autenthic.');
      kohana::log('debug', 'cid: ' . $this->input->post('cid') );
      kohana::log('debug', 'sid: ' . $this->input->post('sid') );
      kohana::log('debug', 'amount: ' . $this->input->post('reward_amount') );
      kohana::log('debug', 'char_id: ' . $this->input->post('char_id') );
      // the message is authentic
    } else {
      kohana::log('debug', 'message is not autenthic.');
    }

    if (0)
    {
      header("HTTP/1.0 400 Bad Request");
      echo "The specified sid is invalid"; // example error message
      exit();
    }
    else
      echo "1";
  }

}